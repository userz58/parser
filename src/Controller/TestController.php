<?php

namespace App\Controller;

use App\Parser\TssParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function index(TssParser $parser): Response
    {
        //$parser->parse();
        dd('stop1');

        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
}
