<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\TranslatorBagInterface;
use Wizaplace\SDK\Translation\TranslationService;
use WizaplaceFrontBundle\Command\PullTranslationsCommand;
use WizaplaceFrontBundle\Command\PushTranslationsCommand;
use WizaplaceFrontBundle\Service\AuthenticationService;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class PushAndPullTranslationsCommandTest extends BundleTestCase
{
    public function testExecuteWithEmptyCatalog()
    {
        $application = new Application(self::$kernel);

        /** @var TranslatorBagInterface|\PHPUnit_Framework_MockObject_MockObject $translatorBag */
        $translatorBag = $this->createMock(TranslatorBagInterface::class);

        $catalog = new MessageCatalogue('fr');
        $translatorBag->expects($this->once())->method('getCatalogue')->with('fr')->willReturn($catalog);

        $application->add(new PushTranslationsCommand(
            $translatorBag,
            static::$kernel->getContainer()->get('translation.dumper.xliff'),
            static::$kernel->getContainer()->get(TranslationService::class),
            static::$kernel->getContainer()->get(AuthenticationService::class),
            [ 'fr' ],
            'en',
            static::$kernel->getContainer()->getParameter('wizaplace.system_user_password')
        ));
        $translationsDir = dirname(stream_get_meta_data(tmpfile())['uri']);
        $application->add(new PullTranslationsCommand(
            static::$kernel->getContainer()->get(TranslationService::class),
            static::$kernel->getContainer()->get(AuthenticationService::class),
            [ 'fr' ],
            $translationsDir
        ));

        $pushCommand = $application->find('wizaplace:translations:push');
        $commandTester = new CommandTester($pushCommand);

        $commandTester->execute(['command'  => $pushCommand->getName()]);

        $pullCommand = $application->find('wizaplace:translations:pull');
        $commandTester = new CommandTester($pullCommand);

        $commandTester->execute(['command'  => $pullCommand->getName()]);

        $this->assertFileExists($translationsDir.'/messages.fr.xliff');
        $pulledCatalog = static::$kernel->getContainer()->get('translation.loader.xliff')->load($translationsDir.'/messages.fr.xliff', 'fr');
        $this->assertCount(0, $pulledCatalog->all());
    }

    public function testExecuteWith2Locales()
    {
        $application = new Application(self::$kernel);

        /** @var TranslatorBagInterface|\PHPUnit_Framework_MockObject_MockObject $translatorBag */
        $translatorBag = $this->createMock(TranslatorBagInterface::class);

        $catalogFr = new MessageCatalogue('fr');
        $catalogFr->set('test_message', 'bonjour');
        $catalogFr->set('food_cheese', 'fromage');
        $catalogEn = new MessageCatalogue('en');
        $catalogEn->set('test_message', 'hello');
        $catalogEn->set('food_meat', 'meat');
        $translatorBag->expects($this->exactly(2))->method('getCatalogue')
            ->withConsecutive(['fr'], ['en'])
            ->willReturnOnConsecutiveCalls($catalogFr, $catalogEn);

        $application->add(new PushTranslationsCommand(
            $translatorBag,
            static::$kernel->getContainer()->get('translation.dumper.xliff'),
            static::$kernel->getContainer()->get(TranslationService::class),
            static::$kernel->getContainer()->get(AuthenticationService::class),
            [ 'fr', 'en' ],
            'en',
            static::$kernel->getContainer()->getParameter('wizaplace.system_user_password')
        ));
        $translationsDir = dirname(stream_get_meta_data(tmpfile())['uri']);
        $application->add(new PullTranslationsCommand(
            static::$kernel->getContainer()->get(TranslationService::class),
            static::$kernel->getContainer()->get(AuthenticationService::class),
            [ 'fr', 'en' ],
            $translationsDir
        ));

        $pushCommand = $application->find('wizaplace:translations:push');
        $commandTester = new CommandTester($pushCommand);

        $commandTester->execute(['command'  => $pushCommand->getName()]);

        $pullCommand = $application->find('wizaplace:translations:pull');
        $commandTester = new CommandTester($pullCommand);

        $commandTester->execute(['command'  => $pullCommand->getName()]);

        $this->assertFileExists($translationsDir.'/messages.fr.xliff');
        $pulledFrCatalog = static::$kernel->getContainer()->get('translation.loader.xliff')->load($translationsDir.'/messages.fr.xliff', 'fr');
        $this->assertSame([
            'messages' =>
            [
                'food_cheese' => 'fromage',
                'test_message' => 'bonjour',
            ],
        ], $pulledFrCatalog->all());

        $this->assertFileExists($translationsDir.'/messages.en.xliff');
        $pulledEnCatalog = static::$kernel->getContainer()->get('translation.loader.xliff')->load($translationsDir.'/messages.en.xliff', 'en');
        $this->assertSame([
            'messages' =>
            [
                'food_meat' => 'meat',
                'test_message' => 'hello',
            ],
        ], $pulledEnCatalog->all());
    }
}
