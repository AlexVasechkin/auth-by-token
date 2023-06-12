# Actions to create authorization via lifetime token

## How it works

### For Symfony framework

First add services to services.yml
```yaml
    Avn\Security\AuthByToken\CreateTokenAction: null
    Avn\Security\AuthByToken\ValidateTokenAction: null
```

Create route to create token for user and send to user email
```php
/**
 * @Route("/api/v1/public/create-token-and-send-by-email", methods={"GET"})
 */
public function authByEmail(
    Request $httpRequest,
    MailerInterface $mailer,
    UserRepository $userRepository,
    CreateTokenAction $createTokenAction,
    ParameterBagInterface $parameterBag,
    UrlGeneratorInterface $urlGenerator
) {
    $user = $userRepository->findOneBy(['email' => $httpRequest->toArray()['email'] ]);

    if (is_null($user)) {
        throw new \Exception(sprintf('User[email: %s] not found', $httpRequest->toArray()['email']));
    }

    $token = $createTokenAction->execute($user->getCode()->toBase32());

    $url =   $parameterBag->get('app.host')
           . $urlGenerator->generate('app_login_from_email', ['token' => $token])
    ;

    $mailer->send(
        (new Email())
            ->from($parameterBag->get('app.email.from'))
            ->to($user->getEmail())
            ->subject('Now you can login')
            ->text('Here is your link to login')
            ->html(sprintf('<p>You can login. Just follow the <a href="%s">link</a>.</p>', $url))
    );

    return new Response();
}
```

Create route to validate token after user follows the link from email
```php
/**
 * @Route("/api/v1/public/login-from-email-link", methods={"POST"}, name="app_login_from_email")
 */
public function register(
    Request $httpRequest,
    ValidateTokenAction $validateTokenAction,
    ParameterBagInterface $parameterBag,
    UserRepository $userRepository
) {
    $response = new RedirectResponse(
        $this->generateUrl('app_dashboard_home')
    );

    $validateTokenAction->execute(
        $httpRequest->get('token'),
        function (AuthTokenData $tokenData, array $payload) use ($response, $parameterBag, $userRepository) {

            $user = $userRepository->findOneByCodeOrFail($payload['sub']);

            $response->headers->setCookie(
                new Cookie(
                    'dashboard-user',
                    $user->getCode(),
                    60 * 60 * 12,
                    '/dashboard',
                    $parameterBag->get('app.host'),
                    true,
                    true
                )
            );
        }
    );

    return $response;
}
```
