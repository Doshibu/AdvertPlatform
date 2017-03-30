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
		$listAdverts = $this->getDoctrine()->getManager()->getRepository('GBPlatformBundle:Advert')->findAll();

    // Et modifiez le 2nd argument pour injecter notre liste
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
			->findBy(array('advert' => $advert));		

		return $this->render('GBPlatformBundle:Advert:view.html.twig', array(
			'advert' => $advert,
			'listApplications' => $listApplications,
			'listAdvertSkills' => $listAdvertSkills
			));
	}

	public function addAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		
		// Création de l'entité Advert
		$advert = new Advert();
		$advert->setTitle('Recherche développeur Symfony2');
		$advert->setAuthor('Alexandre');
		$advert->setContent('Nous recherchons un développeur Symfony2 débutant sur Lyon.');
		
		// Création de l'entité Image
		$image = new Image();
		$image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
		$image->setAlt('Job de rêve');

		// On lie l'image à l'annonce
		$advert->setImage($image);

		// On récupère toutes les compétences possibles
	    $listSkills = $em->getRepository('GBPlatformBundle:Skill')->findAll();

	    // Pour chaque compétence
	    foreach ($listSkills as $skill) 
	    {
	      // On crée une nouvelle « relation entre 1 annonce et 1 compétence »
	      $advertSkill = new AdvertSkill();

	      // On la lie à l'annonce, qui est ici toujours la même
	      $advertSkill->setAdvert($advert);
	      // On la lie à la compétence, qui change ici dans la boucle foreach
	      $advertSkill->setSkill($skill);

	      // Arbitrairement, on dit que chaque compétence est requise au niveau 'Expert'
	      $advertSkill->setLevel('Expert');

	      // Et bien sûr, on persiste cette entité de relation, propriétaire des deux autres relations
	      $em->persist($advertSkill);
	    }

		//Création d'une première candidature
		$application1 = new Application();
		$application1->setAuthor('Marine');
		$application1->setContent('J\'ai toutes les qualités requises.');

		//Création d'une deuxième candidature
		$application2 = new Application();
		$application2->setAuthor('Pierre');
		$application2->setContent('Je suis très motivé.');		

		$application1->setAdvert($advert);
		$application2->setAdvert($advert);

		$em = $this->getDoctrine()->getManager();
		$em->persist($advert);
		$em->persist($application1);
		$em->persist($application2);

		$em->flush();

		if ($request->isMethod('POST')) 
		{
			$request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
			return $this->redirect($this->generateUrl('gb_platform_view',
				array('id' => $advert->getId())));
		}
		return $this->render('GBPlatformBundle:Advert:add.html.twig');
	}

	public function editAction(Request $request, $id)
	{	
		$em = $this->getDoctrine()->getManager();
		$advert = $em
			->getRepository('GBPlatformBundle:Advert')
			->find($id);

		if($advert === null)
		{
			throw new NotFoundHttpException('L\'annonce d\'id '.$id.' n\'existe pas.');		
		}

		// La méthode findAll retourne toutes les catégories de la base de données
		$listCategories = $em
			->getRepository('GBPlatformBundle:Category')
			->findAll();

		// On boucle sur les catégories pour les lier à l'annonce
		foreach ($listCategories as $category) 
		{
		    $advert->addCategory($category);
		}

		$em->flush();

		if ($request->isMethod('POST')) 
		{
			$request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');
			return $this->redirectToRoute('gb_platform_view', array('id' => 5));
		}

		$advert = array(
			'title'   => 'Recherche développpeur Symfony2',
			'id'      => $id,
			'author'  => 'Alexandre',
			'content' => 'Nous recherchons un développeur Symfony2 débutant sur Lyon. Blabla…',
			'date'    => new \Datetime()
			);

		return $this->render('GBPlatformBundle:Advert:edit.html.twig', array(
			'advert' => $advert
		));
	}

	public function deleteAction($id)
	{
		$em = $this->getDoctrine()->getManager();

	    // On récupère l'annonce $id
	    $advert = $em->getRepository('GBPlatformBundle:Advert')->find($id);

	    if (null === $advert) 
	    {
	      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
	    }

	    // On boucle sur les catégories de l'annonce pour les supprimer
	    foreach ($advert->getCategories() as $category) 
	    {
	      $advert->removeCategory($category);
	    }
	    $em->flush();

		return $this->render('GBPlatformBundle:Advert:delete.html.twig');
	}

	public function menuAction($limit)
	{
		// On récupère les annonces du mois
		$listAdverts = $this->getDoctrine()->getManager()->getRepository('GBPlatformBundle:Advert')->findAdvertForCurrentMonth('DESC', $limit);

		return $this->render('GBPlatformBundle:Advert:menu.html.twig', array(
      // Tout l'intérêt est ici : le contrôleur passe
      // les variables nécessaires au template !
			'listAdverts' => $listAdverts
			));
	}
}

?>