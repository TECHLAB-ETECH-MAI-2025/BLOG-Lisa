<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;



class AppFixtures extends Fixture
{
    
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $uploadDir = __DIR__ . '/../../public/uploads/images';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }


    $imageFiles = [];
    for ($i = 1; $i <= 18; $i++) {
        $imageFiles[] = $i . '.png';
    }

        $categories = [];
        for ($i = 0; $i < 20; $i++) {
            $category = new Category();
            $category->setName($faker->word());
            $category->setDescription($faker->text(100));
            $manager->persist($category);
            $categories[] = $category;
        }

        $articles = [];
        for ($i = 0; $i < 20; $i++) {
            $article = new Article();
            $article->setTitle($faker->text(100));
            $article->setContent($faker->paragraph(5));
            $article->setCreatedAt(new \DateTimeImmutable());
            $article->setUpdatedAt(null);

            $article->addCategory($categories[array_rand($categories)]);

            $article->setImage($imageFiles[array_rand($imageFiles)]);

            $manager->persist($article);

            $articles[] = $article;
        }

        foreach ($articles as $article) {
            for ($j = 0; $j < mt_rand(2, 5); $j++) {
                $comment = new Comment();
                $comment->setContent($faker->paragraph());
                $comment->setCreatedAt(new \DateTimeImmutable());
                $comment->setArticle($article);

                $comment->setAuthor($faker->text(100)); 
                
                $manager->persist($comment);
            }
        }

        $manager->flush();
    }
}
