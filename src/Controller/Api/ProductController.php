<?php

namespace App\Controller\Api;

use App\Entity\Customer;
use App\Entity\Product;
use App\Repository\CustomerRepository;
use App\Repository\ProductRepository;
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
use App\Entity\ApiLog;


/**
 * Product API controller.
 * @Route("/api/v1", name="api_")
 * 
 */
class ProductController extends ApiWrapper
{
    /**
     * this would be a Get (all) method according to assigment. Using standard API conventions.
     * @Route("/products", name="api_products_index", methods="GET")
     */
    public function index(Request $request, ProductRepository $productRepository)
    {
        $products = $productRepository->transformAll();
        return $this->respond($products);
    }

    /**
     * this would be a Get/Id method according to assigment. Using standard API conventions.
     * @Route("/products/{id}", methods="GET")
     */
    public function show(Request $request, $id, ProductRepository $productRepository)
    {
        $product = $productRepository->find($id);
        
        return ($product) ? $this->respond($productRepository->transform($product)) : $this->respondNotFound();
    }

    /**
     * 
     * @Route("/products", methods="POST")
     * 
     */
    public function create(Request $request, ProductRepository $productRepository, EntityManagerInterface $em, ValidatorInterface $validator)
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

        $customer = $em->getRepository(Customer::class)->find($request->get('customer'));

        if(!$customer)
            return $this->respondValidationError('Please provide a valid customer!');

        $product = new Product;
        $product->setCustomer($customer);
        $product->setName($request->get('name'));
        $product->setAsin($request->get('asin'));
        $product->setUuid($request->get('uuid'));
        $product->setStatus($request->get('status', 'new'));
        $em->persist($product);
        $em->flush();

        $responseData = $productRepository->transform($product);
        return $this->respondCreated($responseData);
    }

    /**
     * 
     * @Route("/products/{id}", methods="PUT")
     * 
     */
    public function update(Request $request, $id, ValidatorInterface $validator)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) return $this->respondNotFound();

        $violations = $this->_validate($request, $validator);
        if ($violations->count() > 0) {
            $formatedViolationList = [];
            for ($i = 0; $i < $violations->count(); $i++) {
                $violation = $violations->get($i);
                $formatedViolationList[] = array($violation->getPropertyPath() => $violation->getMessage());
            }
            return $this->respond($formatedViolationList);
        }

        $customer = $entityManager->getRepository(Customer::class)->find($request->get('customer'));

        if(!$customer)
            return $this->respondValidationError('Please provide a valid customer!');

        $product->setCustomer($customer);
        $product->setName($request->get('name'));
        $product->setAsin($request->get('asin'));
        $product->setUuid($request->get('uuid'));
        $product->setStatus($request->get('status', 'new'));

        $entityManager->flush();
        $responseData = $entityManager->getRepository(Product::class)->transform($product);

        $this->setStatusCode(200);
        return $this->respond($responseData);
    }

    /**
     * 
     * @Route("/products/{id}", methods="DELETE")
     * 
     */
    public function delete(Request $request, $id, EntityManagerInterface $em, ProductRepository $productRepository)
    {
        $product = $productRepository->find($id);
        if (!$product)
            return $this->respondNotFound();

        $em->remove($product);
        $em->flush();

        
        $this->setStatusCode(204);
        return $this->respond([]);
    }

    private function _validate($request, ValidatorInterface $validator)
    {
        $constraints = new Assert\Collection([
            'customer' => [
                new Assert\NotBlank(),
            ],
            'name' => [
                new Assert\NotBlank(),
            ],
            'asin' => [
                new Assert\NotBlank(),
                new Assert\Length(10)
            ],
            'uuid' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 36])
            ],
            'status' => [
                new Assert\Optional(),
                new Assert\Choice(['new', 'pending', 'approved', 'inactive'])
            ],

        ]);

        $violations = $validator->validate(
            [
                'customer' => $request->get('customer'),
                'name' => $request->get('name'),
                'asin' => $request->get('asin'),
                'uuid' => $request->get('uuid'),
                'status' => $request->get('status'),
            ],
            $constraints
        );

        return $violations;

        
    }
}
