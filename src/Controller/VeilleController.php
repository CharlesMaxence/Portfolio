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
        $rssUrl = 'https://www.silicon.fr/feed';
        
        $articles = [];

        try {
            $response = $this->client->request('GET', $rssUrl);
            $xmlContent = $response->getContent();
            
            $xml = new \SimpleXMLElement($xmlContent);

            foreach ($xml->channel->item as $item) {
                $articles[] = [
                    'title' => (string) $item->title,
                    'link' => (string) $item->link,
                    'description' => (string) $item->description,
                    'date' => (string) $item->pubDate,
                ];
            }
        } catch (\Exception $e) {
            $articles = [];
        }

        return $this->render('veille/index.html.twig', [
            'articles' => $articles,
        ]);
    }
}