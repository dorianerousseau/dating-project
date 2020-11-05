<?php

namespace App\Controller;

use App\Entity\Hobbies;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;


class UserController extends AbstractController
{
    /**
     * Formulaire d'inscription d'un User
     * @Route("/membre/inscription", name="user_create", methods={"GET|POST"})
     *
     */
    public function create_user(Request $request, UserPasswordEncoderInterface $encoder, SluggerInterface $slugger)
    {
        # 1. Création d'un nouvel utilisateur
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        # 2. Création du Formulaire d'inscription
        $form = $this->createFormBuilder($user)
            ->add('pseudo', TextType::class)
            ->add('age', TextType::class)
            ->add('sex', ChoiceType::class, [
                'choices' => [
                    'Choisissez' => true,
                    'Femme' => 'Femme',
                    'Homme' => 'Homme',
                    'Autres' => 'Autres'
                ],
                'choice_label' => function ($choice, $key, $value) {
                    if (true === $choice) {
                        return 'Choisissez';
                    }

                    return ($key);

                }
            ])# liste déroulante (h /f)

            ->add('hobbies', EntityType::class, [
                'class' => Hobbies::class,
                'multiple' => true,
                'choice_label' => 'name',
            ])
            ->add('city', TextType::class)

            ->add('hobbies', EntityType::class, [
                'class' => Hobbies::class,
                'multiple' => true,
                'choice_label' => 'name',
            ])


            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('featuredImage', FileType::class )
            ->add('submit', SubmitType::class,[
                "label"=>"Valider"])
            ->getForm();

        # 3. Récupération des infos
        $form->handleRequest($request);

        # 4. Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            # 4a. On encode le MDP
            $user->setPassword(
                $encoder->encodePassword($user, $user->getPassword())
            );

            # 4b. On gere l'Upload de l'image
            /** @var UploadedFile $featuredImage */
            $featuredImage = $form->get('featuredImage')->getData();

            if ($featuredImage) {
                $originalFilename = pathinfo($featuredImage->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$featuredImage->guessExtension();

                try {
                    $featuredImage->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'featureImagename' property to store the PDF file name
                // instead of its contents

                # on stock dans la BDD
                $user->setFeaturedImage($newFilename);
            }


            # 4d. on sauvegarde en BDD
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            # 4e. Notification Flash
            $this->addFlash('notice', 'Merci pour votre inscription !');

            # 4f. Redirection vers page connexion
            return $this->redirectToRoute('app_login');

        }

        #5. Transmission à la Vue
        return $this->render('user/create.html.twig', [
            'form' =>$form->createView()
        ]);

    }

}



