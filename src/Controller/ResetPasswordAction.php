<?php 

namespace App\Controller;
 
use App\Entity\User;


use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;


class ResetPasswordAction {

    private $validator;
    private $userPasswordEncoder;
    private $entityManager;
    private $tokenManager;

    public function __construct(
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $userPasswordEncoder,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $tokenManager
        )
    {
        $this->validator = $validator;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->entityManager = $entityManager;
        $this->tokenManager = $tokenManager;
    }
    
    public function __invoke(User $data)
    {
        //var_dump($data->getNewPassword(), $data->getNewRetypedPassword(), $data->getOldPassword());
        //die();

        $this->validator->validate($data);

        $data->setPassword(
            $this->userPasswordEncoder->encodePassword($data, $data->getNewPassword())
        );

        $this->entityManager->flush();

        $token = $this->tokenManager->create($data);

        return new JsonResponse(['token' => $token]);
    }

}