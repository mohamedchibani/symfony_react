<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker\Factory;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Comment;


class AppFixtures extends Fixture
{
    private $passwordEncoder;
    private $faker;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        $this->userLoad($manager);
        $this->postLoad($manager);
        $this->commentLoad($manager);
    }


    public function postLoad(ObjectManager $manager){
        
        for($i=0; $i<100; $i++){

            $post = new Post();

            $post->setTitle($this->faker->sentence());
            $post->setSlug($this->faker->slug());
            $post->setContent($this->faker->realText());
            $post->setPublished(new \DateTime());

            

            // get reference
            $user = $this->getReference('user_admin_'. rand(0,9));

            // add reference
            $this->addReference("post_$i", $post);
            
            $post->setAuthor($user);
            
            $manager->persist($post);
            
        }

        $manager->flush();
    }


    public function userLoad(ObjectManager $manager){

        $myRoles = [User::ROLE_ADMIN, User::ROLE_COMMENTATOR, User::ROLE_EDITOR, User::ROLE_WRITER, User::ROLE_SUPERADMIN];


        for($i=0; $i<10; $i++){
            
            $user = new User();

            $user->setUsername($this->faker->userName);
            $user->setName($this->faker->name);
            $user->setPassword($this->passwordEncoder->encodePassword($user, 'secret123'));
            $user->setEmail($this->faker->email);
            $user->setRoles([$myRoles[rand(0,4)]]);

            // add reference
            $this->addReference("user_admin_$i", $user);

            $manager->persist($user);
        }
        $manager->flush();

    }

    public function commentLoad(ObjectManager $manager){
           
        for($i=0; $i<1000; $i++){

            $comment = new Comment();

            $comment->setContent($this->faker->realText());
            $comment->setPublished(new \DateTime());

            // get reference
            $user = $this->getReference('user_admin_'. rand(0,9));
            $post = $this->getReference('post_'. rand(0,99));
            $comment->setAuthor($user);
            $comment->setPost($post);
            
            $manager->persist($comment);
        }

        $manager->flush();
        

    }


}
