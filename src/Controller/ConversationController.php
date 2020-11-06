<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\Message;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Security("chat.checkForUser(user)")
     * @Route("/conversations/{id}", name="conversations_get", methods={"GET|POST"})
     *
     */
    public function get_conversations(Chat $chat, Request $request)
    {
        # 1. Récupération du chat et du message
        $messages = $chat->getMessages();

        $message = new Message();
        $message->setChat($chat);
        $message->setUser($this->getUser());
        $message->setCreatedAt(new \DateTime());

        # 2. Création du Formulaire pour créer un message
        $form = $this->createFormBuilder($message)
            ->add('content', TextareaType::class, [
                'label' => 'Message'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer le message'
            ])
            ->getForm();


        #3. Demande à symfony de récupérer les infos dans la request
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();

            return $this->redirectToRoute('conversations_get', [
                'id' => $chat->getId()
            ]);

        } #endif

        return $this->render('conversation/messages.html.twig', [
            'messages'=>$messages,
            'chat' => $chat,
            'form' => $form->createView()
        ]);
    }

}



