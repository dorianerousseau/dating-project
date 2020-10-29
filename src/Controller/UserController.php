<?php


namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class UserController extends AbstractController
{
    /**
     * Formulaire d'inscription d'un User
     * @Route("/membre/inscription", name="user_create", methods={"GET|POST"})
     *
     */
    public function create_user(Request $request, UserPasswordEncoderInterface $encoder)
    {
        # 1. Création d'un objet user
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        # 2. Création du Formulaire
        $form = $this->createFormBuilder($user)
            ->add('pseudo', TextType::class)
            ->add('age', TextType::class)
            ->add('sex', ChoiceType::class, [
                'choices' => [
                    'femme' => false,
                    'homme' => false,
                ],
                'choice_label' => function ($choice, $key, $value) {
                    if (true === $choice) {
                        return 'Choisissez';
                    }

                    return strtoupper($key);

                    // or if you want to translate some key
                    //return 'form.choice.'.$key;
                }
            ])
        # liste déroulante (h /f)
            ->add('city', TextType::class)
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            #3. Encodage du MDP
            $user->setPassword(
                $encoder->encodePassword($user, $user->getPassword())
            );

            #4. Sauvegarde en BDD
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            #5. Notification Flash
            $this->addFlash('notice', 'Merci pour votre inscription !');

            #6. Redirection FIXME modifier l'url vers page connexion
            return $this->redirectToRoute('index');

        }

        #  la Vue
        return $this->render('user/create.html.twig', [
            'form' =>$form->createView()
        ]);

    }


}


