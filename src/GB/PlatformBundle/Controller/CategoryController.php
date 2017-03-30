<?php

namespace GB\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

use GB\PlatformBundle\Entity\Advert;
use GB\PlatformBundle\Entity\Category;

class CategoryController extends Controller
{
    public function indexAction(Request $request)
    {

    }

    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $catRepo = $em
            ->getRepository('GBPlatformBundle:Category');
        $cat = $catRepo
            ->find($id);

        if($cat === null)
        {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        return $this->render('GBPlatformBundle:Advert:view.html.twig', array(
            'category' => $cat,
            ));
    }
}