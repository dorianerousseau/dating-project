<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\Message;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class ConversationController extends AbstractController
{
    /**
     * Conversation en privé
     * @Route("/discussion/{id}/new", name="conversation_create", methods={"GET|POST"})
     *
     */
    public function create_conversation(User $user)
    {
        # 1. Récupération du chat et du message
        $chat = new Chat();
        $chat->setCreatedAt(new \DateTime());
        $chat->setConversation($user->getPseudo() . ' avec ' . $this->getUser()->getPseudo());
        $chat->addUser($this->getUser());
        $chat->addUser($user);

        $em = $this->getDoctrine()->getManager();
        $em->persist($chat);
        $em->flush();

        return $this->redirectToRoute('conversations_get', [
           'id' => $chat->getId()
        ]);
    }

    /**
     * Conversation en privé
     * TODO : Vérifier que veux quia ccede au chat sont bien les users du chat
     * @Route("/conversations/{id}", name="conversations_get", methods={"GET|POST"})
     *
     */
    public function get_conversations(Chat $chat, Request $request)
    {
        # 1. Récupération du chat et du message
        $messages = $chat->getMessages();

        $message = new Message();
        $message->setUser($this->getUser());
        # TODO : Formulaire pour créer un message


        #1. Ajout de la date du message
        $message->setCreatedAt(new \DateTime());


        # 2. Création du Formulaire pour créer un message
        $form = $this->createFormBuilder($message)
            ->add('content', TextareaType::class)
            ->add('submit', SubmitType::class)
            ->getForm();


        #3. Demande à symfony de récupérer les infos dans la request
        $form->handleRequest($request);

        # TODO : Traitement du formulaire pour ajouter un message

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();
        } #endif

        return $this->render('conversation/messages.html.twig', [
            'messages'=>$messages
        ]);
    }

}



