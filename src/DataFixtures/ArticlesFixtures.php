<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;


class ArticlesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        //Creer 3 categories fakees
        for($i = 1; $i <= 3; $i++){
            $category = new Category();
            $category->setTitle($faker->word())
                     ->setDescription($faker->paragraph());

            $manager->persist($category);

            //Creer entre 4 et 6 articles fakees
            for($j = 1; $j <= mt_rand(4, 6); $j++){
                $article = new Article();

                $content = join($faker->paragraphs(5), '</p><p>');

                $article->setTitle($faker->sentence())
                        ->setContent($content)
                        ->setImage("https://picsum.photos/id/".random_int(1, 200)."/640/480")
                        ->setCreatedAt($faker->dateTimeBetween('-6 months'))
                        ->setCategory($category);

                $manager->persist($article);

                //Commentaires à l'article
                for($k = 1; $k <= mt_rand(4, 10); $k++){
                    $comment = new Comment();

                    $content = '<p>' . join($faker->paragraphs(2), '</p><p>') . '</p>';

                    $days = (new \DateTime())->diff($article->getCreatedAt())->days;

                    $comment->setAuthor($faker->name)
                            ->setContent($content)
                            ->setCreatedAt($faker->dateTimeBetween('-' . $days .'days'))
                            ->setArticle($article);

                    $manager->persist($comment);
                }
            }
        }
        $manager->flush();
    }
}
