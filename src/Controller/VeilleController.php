<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class VeilleController extends AbstractController
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    #[Route('/vt', name: 'app_veille')]
    public function index(): Response
    {
        $rssUrl = 'https://www.smashingmagazine.com/feed/';
        
        $articles = [];

        try {
            // On simule un navigateur avec un User-Agent pour éviter d'être bloqué
            $response = $this->client->request('GET', $rssUrl, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Portfolio-BTS-SIO'
                ]
            ]);
            
            $xmlContent = $response->getContent();
            
            // Désactivation temporaire des erreurs XML strictes si le flux contient des caractères spéciaux
            libxml_use_internal_errors(true);
            $xml = new \SimpleXMLElement($xmlContent);

            // Vérification si le flux utilise la structure standard channel->item
            if (isset($xml->channel->item)) {
                foreach ($xml->channel->item as $item) {
                    $image = null;
                    foreach ($item->enclosure as $enclosure) {
                        if (str_starts_with((string) $enclosure['type'], 'image/')) {
                            $image = (string) $enclosure['url'];
                            if (str_starts_with($image, 'http://')) {
                                $image = 'https://' . substr($image, 7);
                            }
                            break;
                        }
                    }

                    $articles[] = [
                        'title' => (string) $item->title,
                        'link' => (string) $item->link,
                        'description' => (string) $item->description,
                        'date' => (string) $item->pubDate,
                        'image' => $image,
                    ];
                }
            } 
            // Support alternatif si le flux utilise le format Atom (<entry>)
            elseif (isset($xml->entry)) {
                foreach ($xml->entry as $entry) {
                    $articles[] = [
                        'title' => (string) $entry->title,
                        'link' => (string) $entry->link['href'],
                        'description' => (string) $entry->summary,
                        'date' => (string) $entry->updated,
                    ];
                }
            }
        } catch (\Exception $e) {
            // En cas d'erreur, le tableau reste vide mais ne fait pas crasher la page
            $articles = [];
        }

        return $this->render('veille/index.html.twig', [
            'articles' => $articles,
        ]);
    }
}