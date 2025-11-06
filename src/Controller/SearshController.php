<?php
namespace App\Controller;

use App\Entity\Livre;
use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Entity\Editeur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SearshController extends AbstractController
{
    #[Route('/searsh', name: 'app_recherche', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder(null, ['method' => 'GET'])
            ->add('search', TextType::class, [
                'label' => 'Rechercher',
                'required' => false,
                'attr' => ['placeholder' => 'titre, auteur, catégorie...']
            ])
            ->add('btn', SubmitType::class, ['label' => 'Rechercher'])
            ->getForm();

        $form->handleRequest($request);

        $mot = null;
        $results = [
            'livres' => [],
            'auteurs' => [],
            'categories' => [],
            'editeurs' => [],
        ];

        //verifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $mot = $data['search'] ?? null;

            if ($mot && trim($mot) !== '') {
                $mot = trim($mot);
                
                // recherche livres
                $results['livres'] = $em->getRepository(Livre::class)
                    ->createQueryBuilder('l')
                    ->where('l.titre LIKE :mot')
                    ->setParameter('mot', '%'.$mot.'%')
                    ->getQuery()
                    ->getResult();

                //recherche auteurs
                $results['auteurs'] = $em->getRepository(Auteur::class)
                    ->createQueryBuilder('a')
                    ->where('a.nom LIKE :mot OR a.prenom LIKE :mot')
                    ->setParameter('mot', '%'.$mot.'%')
                    ->getQuery()
                    ->getResult();

                // recherche catégories
                $results['categories'] = $em->getRepository(Categorie::class)
                    ->createQueryBuilder('c')
                    ->where('c.designation LIKE :mot')
                    ->setParameter('mot', '%'.$mot.'%')
                    ->getQuery()
                    ->getResult();

                // recherche éditeurs
                $results['editeurs'] = $em->getRepository(Editeur::class)
                    ->createQueryBuilder('e')
                    ->where('e.nom LIKE :mot')
                    ->setParameter('mot', '%'.$mot.'%')
                    ->getQuery()
                    ->getResult();
            }
        }

        return $this->render('searsh/index.html.twig', [
            'form' => $form->createView(),
            'results' => $results,
            'mot' => $mot,
        ]);
    }
}