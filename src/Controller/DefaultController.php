<?php


namespace App\Controller;


use App\Entity\Hobbies;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * Page : Accueil
     */
    public function index()
    {
        # Récupérer les 10 derniers profils de la BDD par ordre décroissant

        $users = $this->getDoctrine()
        ->getRepository(User::class)
        ->findBy([], ['id'=>'DESC'], 10);

        return $this->render('default/index.html.twig', [
            'users' => $users
        ]);
    }


    /**
     * Page / Action : Contact
     */

    # public function contact()
    #{return $this->render('default/contact.html.twig');}



    /**
     * Page / Action : Hobbies
     * Permet d'afficher les articles d'une catégorie
     * @Route("/{alias}", name="default_hobbies", methods={"GET"})
     */
    public function hobbies($alias)
    {
        # Récupération des hobbies via son alias dans l'URL
        $hobbies = $this->getDoctrine()
            ->getRepository(Hobbies::class)
            ->findOneBy(['alias'=>$alias]);

        /*
         * Grâce à la relation entre User et Hobbies
         * (ManyToMany), je suis en mesure de récupérer
         * les utilisateurs selon leurs hobbies
         */
        $users = $hobbies->getUsers();

        return $this->render('default/hobbies.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * Page / Action : Utilisateur
     * Permet d'afficher un utilisateur du site
     * @Route("/user/{id}", name="default_user", methods={"GET"})
     */
    public function user($id)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

        return $this->render('default/users.html.twig', [
            'user'=>$user
        ]);
    }


}