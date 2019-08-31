<?php

namespace App\Controller\Api;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Doctrine\Common\Collections\Collection;
use App\Controller\Api\ApiWrapper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * Customer API controller.
 * @Route("/api/v1", name="api_")
 * 
 */
class CustomerController extends ApiWrapper
{
    /**
     * this would be a Get (all) method according to assigment. Using standard API conventions.
     * @Route("/customers", methods="GET")
     */
    public function index(CustomerRepository $customerRepository)
    {
        $customers = $customerRepository->transformAll();
        return $this->respond($customers);
    }

    /**
     * this would be a Get/Id method according to assigment. Using standard API conventions.
     * @Route("/customers/{id}", methods="GET")
     */
    public function show($id, CustomerRepository $customerRepository)
    {
        $customer = $customerRepository->find($id);
        return $customer ? $this->respond($customerRepository->transform($customer)) : $this->respondNotFound();
    }

    /**
     * 
     * @Route("/customers", methods="POST")
     * 
     */
    public function create(Request $request, CustomerRepository $customerRepository, EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $violations = $this->_validate($request, $validator);
        if ($violations->count() > 0) {
            $formatedViolationList = [];
            for ($i = 0; $i < $violations->count(); $i++) {
                $violation = $violations->get($i);
                $formatedViolationList[] = array($violation->getPropertyPath() => $violation->getMessage());
            }
            return $this->respond($formatedViolationList);
        }
        $customer = new Customer;
        $customer->setFirstName($request->get('first_name'));
        $customer->setLastName($request->get('last_name'));
        $customer->setAge($request->get('age'));
        $customer->setStatus($request->get('status', 'new'));
        $em->persist($customer);
        $em->flush();

        return $this->respondCreated($customerRepository->transform($customer));
    }

    /**
     * 
     * @Route("/customers/{id}", methods="PUT")
     * 
     */
    public function update(Request $request, $id, ValidatorInterface $validator)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $customer = $entityManager->getRepository(Customer::class)->find($id);

        if (!$customer) return $this->respondNotFound();

        $violations = $this->_validate($request, $validator);
        if ($violations->count() > 0) {
            $formatedViolationList = [];
            for ($i = 0; $i < $violations->count(); $i++) {
                $violation = $violations->get($i);
                $formatedViolationList[] = array($violation->getPropertyPath() => $violation->getMessage());
            }
            return $this->respond($formatedViolationList);
        }
        $customer->setFirstName($request->get('first_name'));
        $customer->setLastName($request->get('last_name'));
        $customer->setAge($request->get('age'));
        $customer->setStatus($request->get('status'));

        $entityManager->flush();

        $this->setStatusCode(200);
        return $this->respond($entityManager->getRepository(Customer::class)->transform($customer));
    }


    /**
     * 
     * @Route("/customers/{id}", methods="DELETE")
     * 
     */
    public function delete($id, EntityManagerInterface $em, CustomerRepository $customerRepository)
    {
        $customer = $customerRepository->find($id);
        if (!$customer)
            return $this->respondNotFound();

        $em->remove($customer);
        $em->flush();
        $this->setStatusCode(204);
        return $this->respond([]);
    }

    private function _validate($request, ValidatorInterface $validator)
    {
        $constraints = new Assert\Collection([
            'first_name' => [
                new Assert\NotBlank(),
            ],
            'last_name' => [
                new Assert\NotBlank(),
            ],
            'age' => [
                new Assert\NotBlank()
            ],
            'status' => [
                new Assert\Optional(),
                new Assert\Choice(['new', 'pending', 'approved', 'inactive'])
            ],

        ]);

        $violations = $validator->validate(
            [
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'age' => $request->get('age'),
                'status' => $request->get('status')
            ],
            $constraints
        );

        return $violations;

        
    }
}
