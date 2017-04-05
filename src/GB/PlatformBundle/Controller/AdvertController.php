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
use GB\PlatformBundle\Form\AdvertType;
use GB\PlatformBundle\Form\AdvertEditType;


class AdvertController extends Controller
{
	public function indexAction(Request $request, $page = 1)
	{
		if ($page < 1) 
		{
		    throw $this->createNotFoundException("La page ".$page." n'existe pas.");
		}

		$em = $this->getDoctrine()->getManager();
		$nbPerPage = 8;
		$listAdverts = $em->getRepository('GBPlatformBundle:Advert')
						->getAdverts($page, $nbPerPage);
		$nbPages = ceil(count($listAdverts)/$nbPerPage);

		if ( $page > $nbPages )
		{
			throw $this->createNotFoundException('La page '. $page .' n\'existe pas.');
		}

		return $this->render('GBPlatformBundle:Advert:index.html.twig', array(
			'listAdverts'	=> $listAdverts,
			'nbPages'		=>$nbPages,
			'page' 			=> $page
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
    	/*// La gestion d'un formulaire est particulière, mais l'idée est la suivante :
		if ($request->isMethod('POST')) 
		{
      		// Ici, on s'occupera de la création et de la gestion du formulaire
			$request->getSession()->getFlashBag()->add('info', 'Annonce bien enregistrée.');

      		// Puis on redirige vers la page de visualisation de cet article
			return $this->redirect($this->generateUrl('gb_platform_view', array('id' => 1)));
		}

    	// Si on n'est pas en POST, alors on affiche le formulaire
		return $this->render('GBPlatformBundle:Advert:add.html.twig');*/

		// On crée un objet Advert
	    $advert = new Advert();

	    // On crée le FormBuilder grâce au service form factory
	    $form = $this->createForm(new AdvertType(), $advert);

	    $form->handleRequest($request);
		if ($form->isValid()) 
		{
      		// On l'enregistre notre objet $advert dans la base de données, par exemple
	    	$em = $this->getDoctrine()->getManager();
	    	$em->persist($advert);
	    	$em->flush();

	    	$request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
	      	// On redirige vers la page de visualisation de l'annonce nouvellement créée
		    return $this->redirect($this->generateUrl('gb_platform_view', array('id' => $advert->getId())));
	    }

	    // On passe la méthode createView() du formulaire à la vue
	    // afin qu'elle puisse afficher le formulaire toute seule
	    return $this->render('GBPlatformBundle:Advert:add.html.twig', array(
	      'form' => $form->createView(),
	    ));
	}

	public function editAction(Request $request, $id)
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

		$form = $this->createForm(new AdvertEditType(), $advert);
		$form->handleRequest($request);
		if ($form->isValid()) 
		{
      		// On l'enregistre notre objet $advert dans la base de données, par exemple
	    	$em = $this->getDoctrine()->getManager();
	    	$em->persist($advert);
	    	$em->flush();

	    	$request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');
	      	// On redirige vers la page de visualisation de l'annonce nouvellement créée
		    return $this->redirect($this->generateUrl('gb_platform_view', array('id' => $advert->getId())));
	    }

   		// Ici, on s'occupera de la création et de la gestion du formulaire
		return $this->render('GBPlatformBundle:Advert:edit.html.twig', array(
			'advert' 	=> $advert,
			'form'		=> $form->createView()
		));
	}

	public function deleteAction(Request $request, $id)
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
      		// Si la requête est en POST, on delete l'article
			$request->getSession()->getFlashBag()->add('info', 'Annonce bien supprimée.');

      		// Puis on redirige vers l'accueil
			return $this->redirect($this->generateUrl('gb_platform_home_'));
		}

    	// Si la requête est en GET, on affiche une page de confirmation avant de delete
		return $this->render('GBPlatformBundle:Advert:delete.html.twig', array(
			'advert' => $advert
		));
	}

	public function menuLeftAction($limit)
	{
		// On récupère les annonces du mois
		$listAdverts = $this->getDoctrine()
			->getManager()
			->getRepository('GBPlatformBundle:Advert')
			->findAdvertForCurrentMonth('DESC', $limit);

		return $this->render('GBPlatformBundle:Advert:menuLeft.html.twig', array(
			'listAdverts' => $listAdverts
		));
	}

	public function menuRightAction($limit)
	{
		$listCategory = $this->getDoctrine()
			->getManager()
			->getRepository('GBPlatformBundle:Category')
			->findAll();

		return $this->render('GBPlatformBundle:Advert:menuRight.html.twig', array(
			'listCategory' => $listCategory
		));
	}
}

?>