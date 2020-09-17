<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Basket;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class BasketController extends AbstractController
{
    /**
     * @Route("/panier", name="panier")
     */
    public function all()
    {
        $session = new Session();
        if ($session->get('basket')) {
            $allKeys = array_keys($session->get('basket')->getBasketLines());

            $basketLines = $this->getDoctrine()
                ->getRepository(Article::class)
                ->findById($allKeys);
        } else {
            $basketLines = array();
        }
        return $this->render('basket/index.html.twig', array(
            'lines' => $basketLines,
        ));
    }

    public function orders()
    {

        $session = new Session();
        $em = $this->getDoctrine()->getEntityManager();

        $user = $session->get('user');
        $newUser = $em->getRepository(User::class)->find($user->getId());
        $basket = $user->getBasket();
        $basketLines = $basket->getBasketLines(); //get the content of the current basket

        $total = 0;
        foreach ($basketLines as $line) {
            $total += $line->getArticle()->getPrice() * $line->getQuantity();
        }

        $date = new \DateTime();

        $order = new Orders();
        $order->setDate($date);
        $order->setTotal(strval($total));
        $newUser->addOrders($order);
        $em->flush();

        foreach ($basketLines as $line) {
            $orderLine = new orderLine(); //category not properly saved in the session, so necessary to get the article from the database to avoid creating duplicate on persist
            $article = $em->getRepository(Article::class)->find($line->getArticle()->getId());
            $orderLine->setArticle($article);
            $orderLine->setQuantity($line->getQuantity());
            $order->addOrderLine($orderLine);
            $em->persist($orderLine);
        }
        $newUser->addOrders($order);
        $newBasket = $em->getRepository(Basket::class)->find($user->getBasket()->getId());
        $newBasket->resetBasketLine();
        $em->flush();
        $newUser->setBasket($newBasket);
        $newUser->addOrders($order);

        $session->set('user', $newUser);
        $session->set('content', 0);
        $session->set('total', 0);
    }

    /**
     * @Route("/modifOne/{id}/{price}/{action}", name="modifOne")
     */
    public function modifOne(Request $request, $id, $price, $action)
    {
        $request = Request::createFromGlobals();
        $session = new Session();
        if (!$session->get('basket')) {
            $session->set('basket', new Basket());
        }
        $basket = $session->get('basket');

        $basket->modifBasketLines($id, $price, $action);
        $session->set('basket', $basket);
        // dump($request->headers->get('referer'));

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/delete/{id}/{price}", name="delete")
     */
    public function delete($id, $price)
    {
        $request = Request::createFromGlobals();
        $session = new Session();

        $basket = $session->get('basket');
        $basket->modifBasketLines($id, $price);
        $session->set('basket', $basket);

        return $this->redirectToRoute('panier');
    }
}
