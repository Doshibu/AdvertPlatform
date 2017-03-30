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
        $listCategory = $this->getDoctrine()->getManager()->getRepository('GBPlatformBundle:Category')->findAll();

        if($listCategory === null)
        {
            throw new NotFoundHttpException("Il n'y a actuellement pas de catégories.");
        }

        return $this->render('GBPlatformBundle:Category:index.html.twig', array(
            'listCategory' => $listCategory));
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
            throw new NotFoundHttpException("La catégorie d'id ".$id." n'existe pas.");
        }

        return $this->render('GBPlatformBundle:Category:view.html.twig', array(
            'category' => $cat,
            ));
    }
}