<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wizaplace\SDK\Translation\TranslationService;
use WizaplaceFrontBundle\Service\AuthenticationService;

class PullTranslationsCommand extends Command
{
    /** @var TranslationService */
    private $translationService;

    /** @var AuthenticationService */
    private $authenticationService;

    /** @var string[] */
    private $locales;

    /** @var string */
    private $translationsDir;

    public function __construct(
        TranslationService $translationService,
        AuthenticationService $authenticationService,
        array $locales,
        $translationsDir
    ) {
        $this->translationService = $translationService;
        $this->authenticationService = $authenticationService;
        $this->locales = $locales;
        $this->translationsDir = $translationsDir;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('wizaplace:translations:pull')
            ->setDescription('Pull translations from Wizaplace backend.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        array_walk($this->locales, function (string $locale) use ($io) : void {
            $io->section("Processing locale '$locale'...");
            $this->executeLocale($locale);
            $io->success("'$locale' locale successfully pulled");
        });
    }

    private function executeLocale(string $locale): void
    {
        $xliffCatalog = $this->translationService->getXliffCatalog($locale);

        $catalogFilePath = "{$this->translationsDir}/messages.{$locale}.xliff";
        file_put_contents($catalogFilePath, $xliffCatalog->getContents());
    }
}
