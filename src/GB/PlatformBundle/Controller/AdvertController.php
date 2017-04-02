<?php

namespace GB\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

use GB\PlatformBundle\Entity\Advert;
use GB\PlatformBundle\Entity\Image;
use GB\PlatformBundle\Entity\Application;
use GB\PlatformBundle\Entity\AdvertSkill;

class AdvertController extends Controller
{
	public function indexAction(Request $request, $page = 1)
	{
		if ($page < 1) 
		{
		    throw $this->createNotFoundException("La page ".$page." n'existe pas.");
		}
		$listAdverts = $this->getDoctrine()->getManager()
			->getRepository('GBPlatformBundle:Advert')
			->getAdverts();

		return $this->render('GBPlatformBundle:Advert:index.html.twig', array(
			'listAdverts' => $listAdverts
			));
	}

	public function viewAction($id)
	{
		$em = $this->getDoctrine()->getManager();
		$advert = $em
			->getRepository('GBPlatformBundle:Advert')
			->find($id);

		if($advert === null)
		{
			throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
		}

		$listApplications = $em
			->getRepository('GBPlatformBundle:Application')
			->findBy(array('advert' => $advert));

		$listAdvertSkills = $em
			->getRepository('GBPlatformBundle:AdvertSkill')
			->findByAdvert($advert);		

		return $this->render('GBPlatformBundle:Advert:view.html.twig', array(
			'advert' => $advert,
			'listApplications' => $listApplications,
			'listAdvertSkills' => $listAdvertSkills
			));
	}

	public function addAction(Request $request)
	{
    	// La gestion d'un formulaire est particulière, mais l'idée est la suivante :
		if ($request->isMethod('POST')) 
		{
      		// Ici, on s'occupera de la création et de la gestion du formulaire
			$request->getSession()->getFlashBag()->add('info', 'Annonce bien enregistrée.');

      		// Puis on redirige vers la page de visualisation de cet article
			return $this->redirect($this->generateUrl('gb_platform_view', array('id' => 1)));
		}

    	// Si on n'est pas en POST, alors on affiche le formulaire
		return $this->render('GBPlatformBundle:Advert:add.html.twig');
	}

	public function editAction($id)
	{
    	// On récupère l'EntityManager
		$em = $this->getDoctrine()->getManager();

   		// On récupère l'entité correspondant à l'id $id
		$advert = $em->getRepository('GBPlatformBundle:Advert')->find($id);

    	// Si l'annonce n'existe pas, on affiche une erreur 404
		if ($advert == null) 
		{
			throw $this->createNotFoundException("L'annonce d'id ".$id." n'existe pas.");
		}

   		// Ici, on s'occupera de la création et de la gestion du formulaire
		return $this->render('GBPlatformBundle:Advert:edit.html.twig', array(
			'advert' => $advert
			));
	}

	public function deleteAction($id, Request $request)
	{
    	// On récupère l'EntityManager
		$em = $this->getDoctrine()->getManager();

    	// On récupère l'entité correspondant à l'id $id
		$advert = $em->getRepository('GBPlatformBundle:Advert')->find($id);

    	// Si l'annonce n'existe pas, on affiche une erreur 404
		if ($advert == null) 
		{
			throw $this->createNotFoundException("L'annonce d'id ".$id." n'existe pas.");
		}

		if ($request->isMethod('POST')) 
		{
      		// Si la requête est en POST, on deletea l'article
			$request->getSession()->getFlashBag()->add('info', 'Annonce bien supprimée.');

      		// Puis on redirige vers l'accueil
			return $this->redirect($this->generateUrl('gb_platform_home_'));
		}

    	// Si la requête est en GET, on affiche une page de confirmation avant de delete
		return $this->render('GBPlatformBundle:Advert:delete.html.twig', array(
			'advert' => $advert
			));
	}

	public function menuAction($limit)
	{
		// On récupère les annonces du mois
		$listAdverts = $this->getDoctrine()->getManager()->getRepository('GBPlatformBundle:Advert')->findAdvertForCurrentMonth('DESC', $limit);

		return $this->render('GBPlatformBundle:Advert:menu.html.twig', array(
			'listAdverts' => $listAdverts
			));
	}
}

?>