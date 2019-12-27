<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\FormFactoryInterface;
use ApiPlatform\Core\Validator\ValidatorInterface;
use ApiPlatform\Core\Validator\Exception\ValidationException;


class UploadImageAction{

    private $formFacory;
    private $entityManager;

    public function __construct(
        FormFactoryInterface $formFacory,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    
        )
    {
        $this->formFacory = $formFacory;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    public function __invoke(Request $request)
    {

        // Create a new image instance
        $image = new Image();

        // Validate the form
        $form = $this->formFacory->create(ImageType::class, $image);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->entityManager->persist($image);
            $this->entityManager->flush();

            $image->setFile(null);
            return $image;
        }

        throw new ValidationException(
            $this->validator->validate($image)
        );
    }

}