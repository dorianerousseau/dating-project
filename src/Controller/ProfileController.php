<?php


namespace App\Controller;

use App\Entity\User;
use App\Form\EditProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profil", name="profile")
     */
    public function index()
    {
        $profile = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        return $this->render('profile/profile.html.twig', [
            'user' => $profile
        ]);
    }


    # ------- Pour modifier le profil -------
    /**
     * @Route("/profil/modifier", name="profile_update", methods={"GET"})
     */
    public function editProfile(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(EditProfileType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('message', 'Profil mis à jour');
            return $this->redirectToRoute('user');
        }

        return $this->render('profile/editprofile.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    # ------- Pour modifier le mot de passe -------
    /**
     * @Route("/profil/password/modifier", name="profile_password_update")
     */
    public function editPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();

            $user = $this->getUser();

            // On vérifie si les 2 mots de passe sont identiques
            if ($request->request->get('mdp') == $request->request->get('mdp2')) {
                $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('mdp')));
                $em->flush();
                $this->addFlash('message', 'Mot de passe mis à jour avec succès');

                return $this->redirectToRoute('user');
            } else {
                $this->addFlash('error', 'Les deux mots de passe ne sont pas identiques');
            }
        }

        return $this->render('profile/editpassword.html.twig');
    }


}