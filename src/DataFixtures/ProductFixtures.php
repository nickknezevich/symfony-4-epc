<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Product;
use Faker\Factory;

class ProductFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        \Bezhanov\Faker\ProviderCollectionHelper::addAllProvidersTo($faker);


        for ($i = 0; $i < 1500; $i++) {
            $product = new Product;
            $product->setCustomer($this->getReference(CustomerFixtures::CUSTOMER_REFERENCE));
            $product->setName($faker->productName);
            $product->setAsin($faker->isbn10);
            $product->setUuid($faker->uuid);
            $product->setStatus('new');
    
            $manager->persist($product);
        }

        $manager->flush();
       
    }
}
