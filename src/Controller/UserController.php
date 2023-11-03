<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/registration', name: 'userRegistration')]
    public function indexRegistration()
    {
        $msg = '';

        return $this->render('user/registration.html.twig', [
            'msg' => $msg,
        ]);
    }

    #[Route('/save', name: 'saveRegistration')]
    public function saveRegistration(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $email = $request->get('_username');
        $password = $request->get('_password');
        $name = $request->get('_name');

        if(!empty($email) && !empty($password) && !empty($name)) {
            $plaintextPassword = $password;

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $password
            );

            $user->setEmail($email);
            $user->setPassword($hashedPassword);
            $user->setName($name);
            $user->setRoles(['ROLE_USER']);
            $user->setCreateAt(new \DateTimeImmutable());
            $user->setUpdatedAt(new \DateTimeImmutable());
            $user->setDeletedAt(new \DateTimeImmutable());
            $this->em->persist($user);
            $this->em->flush();

            return $this->redirectToRoute('app_login');

        } else {
            $msg = 'Los campos no pueden estar vacios';

            return $this->render('user/registration.html.twig', [
                'msg' => $msg,
            ]);
        }
     
    }

    #[Route('/back', name: 'backLogin')]
    public function backLogin()
    {
        return $this->redirectToRoute('app_login');
    }
}
