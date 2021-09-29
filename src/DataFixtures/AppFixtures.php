<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Space;
use App\Entity\Answer;
use DateTimeImmutable;
use App\Entity\Comment;
use App\Entity\Question;
use App\Entity\SubComment;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(UserPasswordHasherInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr-FR');

        $spaceList = [];
        $userList = [];

        //create fake Spaces
        for ($i = 0; $i < 10; $i++) {

            $space = new Space;
            $space
                ->setName($faker->word)
                ->setDescription($faker->paragraph(4));
            $manager->persist($space);
            $spaceList[] = $space;
        }

        //create fake users
        for ($i = 1; $i < 15; $i++) {

            $user = new User;
            $user
                ->setEmail($faker->email())
                ->setPassword($this->passwordEncoder->hashPassword($user, 'demo'))
                ->setDescription($faker->paragraph(3))
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setQualification($faker->sentence(2))
                ->setPseudonym($faker->userName)
                ->setImageName('image-' . $i . '.jpg')
                ->setRoles(['ROLE_USER']);
            for ($j = 0; $j < mt_rand(0, count($spaceList)); $j++) {

                $user->addSubscribedSpace($spaceList[mt_rand(0, count($spaceList) - 1)]);
            }
            $manager->persist($user);
            $userList[] = $user;
        }

        foreach ($userList as $user) {
            for ($i = 0; $i < mt_rand(1, count($userList)); $i++) {
                $user->addSubscription($userList[mt_rand(0, count($userList) - 1)]);
            }
            $manager->persist($user);
        }
        //create fake questions
        for ($a = 0; $a < 15; $a++) {
            $question = new Question;
            $question
                ->setQuestion($faker->sentence . '?')
                ->setAuthor($userList[mt_rand(0,count($userList) - 1)])
                ->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTimeAD()));
            for ($b = 0; $b < mt_rand(1, 4); $b++) {
                $question->addSpace($spaceList[mt_rand(0, count($userList) - 1)]);
            }
            $manager->persist($question);

            //create fake Answers
            for ($b = 0; $b < mt_rand(2, 3); $b++) {
                $answer = new Answer;
                $answer
                    ->setViewsNumber(mt_rand(1000, 200000))
                    ->setQuestion($question)
                    ->setAuthor($userList[mt_rand(0, count($userList) - 1)])
                    ->setAnswer('<img src="https://picsum.photos/900/320">' . '<p>' . implode('</p><p>', $faker->paragraphs(15)));

                for ($c = 0; $c < mt_rand(0, 5); $c++) {
                    if (mt_rand(0, 1)) {
                        $answer->addLikedUser($userList[mt_rand(0, count($userList) - 1)]);
                    } else {
                        $answer->addDislikedUser($userList[mt_rand(0, count($userList) - 1)]);
                    }
                }
                $manager->persist($answer);

                //create fake Comments
                for ($c = 0; $c < mt_rand(4, 6); $c++) {
                    $comment = new Comment;
                    $comment
                        ->setAnswer($answer)
                        ->setComment('<p>' . implode('</p><p>', $faker->paragraphs(mt_rand(4, 8))))
                        ->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTimeAD()))
                        ->setAuthor($userList[mt_rand(0, count($userList) - 1)]);

                    $manager->persist($comment);

                    //create fake sub_comments
                    for ($d = 0; $d < mt_rand(0,2); $d++) {
                        $subComment = new SubComment;
                        $subComment
                            ->setAuthor($userList[mt_rand(0, count($userList) - 1)])
                            ->setComment($comment)
                            ->setSubComment('<p>' . implode('</p><p>', $faker->paragraphs(mt_rand(4, 8))));
                        $manager->persist($subComment);
                    }
                }
            }
        }

        $manager->flush();
    }
}
