<?php


namespace App\Controller;


use App\Entity\Hobbies;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * Page Index : quand on arrive sur le site
     */
    public function index()
    {
        if($this->getUser()){
            return $this->redirectToRoute('default_homepage');
        }
        return $this->render('default/index.html.twig');
    }

    /**
     * Page : Homepage (quand on est connecté)
     * @Route("/homepage", name="default_homepage", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function homepage()
    {
        # Récupére les 10 derniers profils de la BDD par ordre décroissant
        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findProfils($this->getUser()->getId());

        return $this->render('default/homepage.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * Page / Action : Hobbies
     * Permet d'afficher les hobbies des utilisateurs
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
        $users = $hobbies->getUsers()->filter(function($user) {
            return $user->getId() !== $this->getUser()->getId();
        });


        return $this->render('default/hobbies.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * Page / Action : Utilisateur
     * Permet d'afficher un utilisateur du site
     * @Route("/user/profil/{id}", name="default_user", methods={"GET"})
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

    /**
     * @Route("/chat/mes-conversations", name="default_mesconvs");
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function mesConversations()
    {
        return $this->render('conversation/chats.html.twig', [
            'chats' => $this->getUser()->getChats()
        ]);
    }


}