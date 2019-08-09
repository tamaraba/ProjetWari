<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Compte;
use App\Entity\Partenaire;
use App\Form\PartenaireType;
use App\Repository\PartenaireRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/api/partenaire")
 */
class PartenaireController extends AbstractController
{
    /**
     * @Route("/", name="partenaire_index", methods={"GET"})
     */
    public function index(PartenaireRepository $partenaireRepository): Response
    {
        return $this->render('partenaire/index.html.twig', [
            'partenaires' => $partenaireRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="partenaire_new", methods={"GET","POST"})
     */
    public function new(Request $request,UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $partenaire = new Partenaire();
        $form = $this->createForm(PartenaireType::class, $partenaire);
        $form->handleRequest($request);
        $data=json_decode($request->getContent(),true);
        $form->submit($data);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($partenaire);
            $entityManager->flush();

        $user = new User();
        $user ->setEmail($partenaire->getEmail());
        $user ->setRoles(['ROLE_AdminPartenaire']);
        $user->setPassword(
            $passwordEncoder->encodePassword(
                $user,
                "MDP123"
                )
        );
        $user ->setNonComplet($partenaire->getNomComplet());
        $user ->setCni($partenaire->getCni());
        $user ->setTelephone($partenaire->getTelephone());
        $user ->setAdresse($partenaire->getAdresse());
        $user ->setStatut("Actif");
        $user ->setPartenaire($partenaire);
        $entityManager->persist($user);
        $entityManager->flush();

        $compte = new Compte();
        $compte ->setNumCompte(date("Y").date("m").date("d").date("H").date("i").date("s"));
        $compte ->setMontant(0);
        $compte ->setPartenaire($partenaire);
        $entityManager->persist($compte);
        $entityManager->flush();

            return new Response('le partenaire a été bien ajouté', Response::HTTP_CREATED);       
    }

    /**
     * @Route("/{id}", name="partenaire_show", methods={"GET"})
     */
    public function show(Partenaire $partenaire): Response
    {
        return $this->render('partenaire/show.html.twig', [
            'partenaire' => $partenaire,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="partenaire_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Partenaire $partenaire): Response
    {
        $form = $this->createForm(Partenaire1Type::class, $partenaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('partenaire_index');
        }

        return $this->render('partenaire/edit.html.twig', [
            'partenaire' => $partenaire,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="partenaire_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Partenaire $partenaire): Response
    {
        if ($this->isCsrfTokenValid('delete'.$partenaire->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($partenaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('partenaire_index');
    }
}
