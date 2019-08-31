<?php
namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Response;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends FOSRestController
{
  private $client_manager;
  public function __construct(ClientManagerInterface $client_manager)
  {
    $this->client_manager = $client_manager;
  }

  /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
            $this->redirectToRoute('admin');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        //throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }

  /**
   * Create Client.
   * @FOSRest\Post("/oauth/createClient")
   *
   * @return Response
   */
  public function AuthenticationAction(Request $request)
  {
    $data = json_decode($request->getContent(), true);
    if (empty($data['redirect-uri']) || empty($data['grant-type'])) {
      return $this->handleView($this->view($data));
    }
    $clientManager = $this->client_manager;
    $client = $clientManager->createClient();
    $client->setRedirectUris([$data['redirect-uri']]);
    $client->setAllowedGrantTypes([$data['grant-type']]);
    $clientManager->updateClient($client);
    $rows = [
      'client_id' => $client->getPublicId(), 'client_secret' => $client->getSecret()
    ];
    return $this->handleView($this->view($rows));
  }
}