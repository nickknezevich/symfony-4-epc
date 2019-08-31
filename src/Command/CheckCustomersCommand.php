<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use App\Entity\Customer;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;

class CheckCustomersCommand extends ContainerAwareCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:check-customers';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription("check the customers which have more than 3 products with status \"pending\" and mark them as \"pending\" ");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $sql = "UPDATE customer 
        INNER JOIN 
        (
          SELECT count(*) as count, product.customer_id, product.status FROM
        product
        INNER JOIN customer on product.customer_id = customer.id
        GROUP BY customer.id, product.status
        HAVING product.status = 'pending' and count >=3
        ) product ON customer.id = product.customer_id
        SET customer.status = 'pending'";
        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();
    
        $output->write('Completed.');
    }
}
