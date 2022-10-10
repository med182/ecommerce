<?php

namespace App\Controller;

use App\Entity\Category;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/category', name: 'category_')]
class CategoryController extends AbstractController{


    #[Route('/{slug}', name: 'list')]
    public function list(Category $category): Response
    {
       $products=$category->getProducts();
        return $this->render('category/list.html.twig',
            compact('category','products')
        );
     
    }
}