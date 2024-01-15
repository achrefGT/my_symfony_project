<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Contact;
use App\Entity\Category;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;




class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repoArticle, CategoryRepository $repoCategory): Response
    {
        $articles = $repoArticle->findAll();
        $categories = $repoCategory->findAll();


        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/",name="home")
     */
    public function home(ArticleRepository $repoArticle) {
        $featuredArticles = $repoArticle->findBy(['isFeatured' => true], ['createdAt' => 'DESC'], 3);
        
        return $this->render('blog/home.html.twig', [
            'featuredArticles' => $featuredArticles,
        ]);
    }

    /**
     * @IsGranted("ROLE_EDITOR")
     * @Route("/blog/new", name = "blog_create")
     * @Route("/blog/{id}/edit", name = "blog_edit")
     */
    public function form(Article $article = null, Request $request, EntityManagerInterface $manager) {
        if(!$article){
            $article = new Article();
        }

        //$form = $this->createFormBuilder($article)
        //            ->add('title',TextType::class)
        //            ->add('content',TextareaType::class)
        //            ->add('image',TextType::class)
        //            ->getForm();

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            if(!$article->getId()){
                $article->setCreatedAt(new \DateTime());
            }

            $manager->persist($article);
            $manager->flush();
            return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
        }

        return $this->render('blog/create.html.twig',[
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() !== null
        ]);
    }

    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article, Request $request, EntityManagerInterface $manager){
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $comment->setCreatedAt(new \DateTime)
                    ->setArticle($article);

            $manager->persist($comment);
            $manager->flush();
            
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);

        }

        return $this->render('blog/show.html.twig', [
            'article' => $article,
            'commentForm' => $form->createView()
        ]);
    }

     /**
     * @Route("/contact", name="contact")
     */
    public function contact(Request $request, EntityManagerInterface $manager){
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($contact);
            $manager->flush();
        }
        return $this->render('blog/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/category/{title}", name="show_category")
     */
    public function showByCategory(CategoryRepository $repo, string $title)
    {
        $category = $repo->findOneBy(['title' => $title]);
        $categories = $repo->findAll();

        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        $articles = $category->getArticles();

        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }   

}
