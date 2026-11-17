<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Tests;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model;
use Contao\Template;
use Doctrine\DBAL\Connection;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Hofff\Contao\ContactProfiles\Tests\Fixtures\RecordingProfile;
use ReflectionClass;
use ReflectionProperty;

trait CreateObjectsTrait
{
    /**
     * Creates a model without touching the Contao framework and assigns the given row data.
     *
     * @param class-string<T>      $modelClass
     * @param array<string, mixed> $data
     *
     * @return T
     *
     * @template T of Model
     */
    protected function createModel(string $modelClass, array $data = []): Model
    {
        $model = (new ReflectionClass($modelClass))->newInstanceWithoutConstructor();
        (new ReflectionProperty(Model::class, 'arrData'))->setValue($model, $data);

        return $model;
    }

    /**
     * @param class-string<T>      $templateClass
     * @param array<string, mixed> $data
     *
     * @return T
     *
     * @template T of Template
     */
    protected function createTemplate(string $templateClass, string $name, array $data = []): Template
    {
        $template = (new ReflectionClass($templateClass))->newInstanceWithoutConstructor();
        $template->setName($name);
        $template->setData($data);

        return $template;
    }

    /**
     * Creates a real profile repository which delegates its queries to the recording profile model.
     */
    protected function createProfileRepository(bool $previewMode = false): ProfileRepository
    {
        RecordingProfile::reset();

        $tokenChecker = $this->createStub(TokenChecker::class);
        $tokenChecker->method('isPreviewMode')->willReturn($previewMode);

        return new ProfileRepository(
            RecordingProfile::class,
            $this->createStub(Connection::class),
            $tokenChecker,
        );
    }
}
