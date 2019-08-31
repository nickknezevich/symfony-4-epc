<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Customer;
use Faker\Factory;

class CustomerFixtures extends Fixture
{
    public const CUSTOMER_REFERENCE = 'customer';


    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for ($i = 0; $i < 1500; $i++) {
            $customer = new Customer();
            $customer->setFirstName($faker->firstNameMale);
            $customer->setLastName($faker->lastName());
            $customer->setAge(rand(18, 85));
            $customer->setStatus('new');
            
            $manager->persist($customer);
        }

        $manager->flush();
        $this->addReference(self::CUSTOMER_REFERENCE, $customer);
 
    }
}
