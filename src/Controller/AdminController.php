<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Repository\ContactRepository;
use App\Entity\User;
use App\Entity\Contact;
use App\Form\EditUserType;


/**
 * @Route("/admin", name="admin_")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="accueil")
     */
    public function index()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/utilisateurs", name="utilisateurs")
     */
    public function usersList(UserRepository $users)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/users.html.twig', [
            'users' => $users->findAll(),
        ]);
    }

    /**
     * @Route("/utilisateurs/modifier/{id}", name="modifier_utilisateur")
     */
    public function editUser(User $user, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('message', 'Utilisateur modifié avec succès');
            return $this->redirectToRoute('admin_utilisateurs');
        }
        
        return $this->render('admin/edituser.html.twig', [
            'userForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/contacts", name="contact_list")
     */
    public function contactList(ContactRepository $contactRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $contacts = $contactRepository->findAll();

        return $this->render('admin/contacts.html.twig', [
            'contacts' => $contacts,
        ]);
    }

    /**
     * @Route("/contacts/delete/{id}", name="delete_contact")
     */
    public function deleteContact(Contact $contact)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($contact);
        $entityManager->flush();

        $this->addFlash('success', 'Message deleted successfully');

        return $this->redirectToRoute('admin_contact_list');
    }
}
