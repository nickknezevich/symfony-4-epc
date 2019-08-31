<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    // Option to transform the Entity to desired format instead of sending all the info.  
    public function transform(Customer $customer)
    {
        return [
            'id'    => (int) $customer->getId(),
            'first_name' => (string) $customer->getfirstName(),
            'last_name' => (string) $customer->getLastName(),
            'age' => (integer) $customer->getAge(),
            'status' => (string) $customer->getStatus(),
            'products' => $customer->getProducts()
        ];
    }

    public function transformAll()
    {
        $customers = $this->findAll();
        $customersArray = [];

        foreach ($customers as $customer) {
            $customersArray[] = $this->transform($customer);
        }

        return $customersArray;
    }

    // /**
    //  * @return Customer[] Returns an array of Customer objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Customer
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
