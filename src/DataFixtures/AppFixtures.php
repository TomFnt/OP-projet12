<?php

namespace App\DataFixtures;

use App\Entity\Tips;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Create 9 random Employees
        for ($i = 0; $i < 9; ++$i) {
            $firstName = $faker->firstName();
            $surName = $faker->lastName();
            $email = $firstName.'.'.$surName.'@'.$faker->freeEmailDomain();

            // custom random list of city and associated country, in order to test different weather units
            $city_list = ['Paris', 'Tokyo', 'Sydney', 'CapeTown', 'Helsinki', 'Toronto', 'Mumbai', 'Lima', 'Reykjavik'];
            $country_list = ['France', 'Japon', 'Australie', 'Afrique du sud', 'Finlande', 'Canada', 'Inde', 'Pérou', 'Islande'];

            $user = new User();
            $user->setLogin($email);
            $user->setPassword($faker->password());
            $user->setCity($city_list[$i]);
            $user->setCountry($country_list[$i]);

            $manager->persist($user);
        }

        // Create 24 random Tips
        for ($i = 0; $i < 24; ++$i) {
            $nb_words = $faker->numberBetween(5, 17);
            $n = $i + 1;

            $tip = new Tips();
            $tip->setDescription('Tips n°'.$n.' : '.$faker->sentence($nb_words, true));
            $manager->persist($tip);
        }
        $manager->flush();
    }
}
