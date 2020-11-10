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
        # ------- Pour modifier le profil -------
    /**
     * @Route("accounts/edit", name="profile_update", methods={"GET|POST"})
     */
    public function editProfile(Request $request)
    {
        # 1. Récupération de l'utilisateur
        $user = $this->getUser();

        # 2. Création du Formulaire de modification selon la class du directory Form
        $form = $this->createForm(EditProfileType::class, $user);

        # 3. Récupération des infos
        $form->handleRequest($request);

        # 4. Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            # 4a. on sauvegarde en BDD
            /*
            * Qu'est ce que le Entity Manager (em) ?
            * C'est une classe qui sait comment sauvegarder d'autres classes.
            */
            $em = $this->getDoctrine()->getManager(); # Récupération de l' Entity Manager
            $em->flush(); # Exécution

            # 4b. Notification Flash
            $this->addFlash('notice', 'Votre profil est mis à jour');

            # 4c. Redirection
            return $this->redirectToRoute('profile_update');
        }

        #5. Transmission à la Vue
        return $this->render('profile/editprofile.html.twig', [
            'form' => $form->createView(),
        ]);

    }


         # ------- Pour modifier le mot de passe -------
    /**
     * @Route("accounts/password/edit", name="profil_password_update")
     */
    public function editPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        # On verifie qu'on est en methode post
        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();

            #on récupere le user pour récuperer son ancien mdp
            $user = $this->getUser();

            #On vérifie si les 2 mots de passe sont identiques
            if ($request->request->get('mdp') == $request->request->get('mdp2')) {
                #On va pour voir encoder l'un des mdp pour le stocker
                $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('mdp')));
                $em->flush(); # pour le mettre a jour dans la bdd
                $this->addFlash('notice', 'votre mot de passe a été mis à jour');

                return $this->redirectToRoute('profile_update');
            } else {
                $this->addFlash('error', 'Les deux mots de passe ne sont pas identiques');
            }
        }

        return $this->render('profile/editprofile.html.twig');
    }



}