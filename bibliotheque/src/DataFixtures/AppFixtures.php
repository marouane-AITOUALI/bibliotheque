<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Livre;
use App\Entity\Genre;
use App\Entity\Auteur;
use App\Entity\Discussion;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordHasherInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        // Créer des genres
        $genres = ['Science Fiction', 'Fantasy', 'Histoire', 'Policier'];
        $genreObjects = [];
        foreach ($genres as $genreName) {
            $genre = new Genre();
            $genre->setNom($genreName);
            $manager->persist($genre);
            $genreObjects[] = $genre;
        }

        // Créer des auteurs
        $auteurs = [
            ['Isaac', 'Asimov'],
            ['J.R.R.', 'Tolkien'],
            ['Arthur', 'Conan Doyle'],
            ['Agatha', 'Christie'],
            ['George', 'Orwell'],
            ['Philip K.', 'Dick'],
            ['J.K.', 'Rowling'],
            ['Ray', 'Bradbury'],
            ['Stephen', 'King'],
            ['Margaret', 'Atwood']
        ];
        $auteurObjects = [];
        foreach ($auteurs as [$prenom, $nom]) {
            $auteur = new Auteur();
            $auteur->setPrenom($prenom);
            $auteur->setNom($nom);
            $manager->persist($auteur);
            $auteurObjects[] = $auteur;
        }

        // Créer des utilisateurs
        $userObjects = [];
        for($j = 0; $j < 10; $j++){
            $user = new User();
            $user->setUsername("user$j");
            $user->setEmail("user$j@example.com");
            $user->setPassword($this->passwordEncoder->hashPassword($user, 
            "password"));
            $user->setRoles(["ROLE_USER"]);
            $manager->persist($user);
            $userObjects[] = $user;
        }

        // Créer des livres
        for ($i = 0; $i < 10; $i++) {
            $livre = new Livre();
            $livre->setTitre('Livre ' . ($i + 1));
            $livre->setImage("https://picsum.photos/1920/1080?random=$i"); // URL de l'image
            $livre->setAuteur($auteurObjects[$i % count($auteurObjects)]);
            $livre->setGenre($genreObjects[$i % count($genreObjects)]);
            $livre->setResume('Résumé du livre ' . ($i + 1));
            $livre->setDatePublication(new \DateTime('-' . (rand(1, 10)) . ' years'));
            $manager->persist($livre);

            // Créer des discussions pour chaque livre
            $discussion1 = new Discussion();
            $discussion1->setLivre($livre);
            $discussion1->setSujet("J\'ai adoré le livre $i!");
            $discussion1->setMessage('Le livre est super, j\'ai adoré chaque page.');
            $discussion1->setDateCreation(new \DateTime('-2 days'));
            // Assigner un auteur à la discussion (utilisateur aléatoire)
            $discussion1->setAuteur($userObjects[array_rand($userObjects)]);
            $manager->persist($discussion1);

            $discussion2 = new Discussion();
            $discussion2->setLivre($livre);
            $discussion2->setSujet('Très intéressant mais...');
            $discussion2->setMessage('J\'ai trouvé la fin un peu décevante, mais le reste était bien.');
            $discussion2->setDateCreation(new \DateTime('-5 days'));
            // Assigner un auteur à la discussion (utilisateur aléatoire)
            $discussion2->setAuteur($userObjects[array_rand($userObjects)]);
            $manager->persist($discussion2);

            $livre->addDiscussion($discussion1);
            $livre->addDiscussion($discussion2);
        }

        // Créer un admin
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@example.com');
        $admin->setPassword($this->passwordEncoder->hashPassword($admin, 'password'));
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        // Créer un utilisateur banni
        $bannedUser = new User();
        $bannedUser->setUsername('bannedUser');
        $bannedUser->setEmail('banned@example.com');
        $bannedUser->setPassword($this->passwordEncoder->hashPassword($bannedUser, 'password'));
        $bannedUser->setRoles(['ROLE_BANNED']);
        $manager->persist($bannedUser);

        // Enregistrer les données
        $manager->flush();
    }
}
