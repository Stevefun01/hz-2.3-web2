<?php

namespace Slimfony\Routing;

use App\Entity\Interface\UserInterface;
use Slimfony\HttpFoundation\Request;
use Slimfony\HttpFoundation\Response;
use Slimfony\HttpKernel\Kernel;
use Slimfony\Templating\Template;
use Slimfony\DependencyInjection\Container;

abstract class AbstractController
{
    private Template $template;
    private ?Request $request = null;

    /**
     * @param Container $container
     */
    public function __construct(
        protected Container $container,
    ) {
        $this->template = $this->container->get(Template::class);
    }

    /**
     * @param string $viewPath
     * @param array<string, mixed> $parameters
     * @return Response
     */
    public function render(string $viewPath, array $parameters = []): Response
    {
        return new Response($this->template->render($viewPath, $parameters, [
            'request' => $this->getRequest(),
            'user' => $this->getUser(),
        ]));
    }

    public function getRequest(): Request
    {
        if ($this->request === null) {
            $this->request = Kernel::$request ?? throw new \LogicException('Kernel did not set a request');
        }

        return $this->request;
    }

    public function setUser(UserInterface $user): void
    {
        $this->getRequest()->getSession()->set('_user', serialize($user));
    }

    public function getUser(): ?UserInterface
    {
        if (!$this->getRequest()->getSession()->has('_user')) {
            return null;
        }

        return unserialize($this->getRequest()->getSession()->get('_user'), UserInterface::class);
    }
}
