<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Application;

use App\Catalog\Application\Characteristic\AdminUseCases\CreateChar\CreateCharHandler;
use App\Catalog\Application\Characteristic\AdminUseCases\DeleteChar\DeleteCharHandler;
use App\Catalog\Application\Characteristic\AdminUseCases\Exception\CharAlreadyInUseException;
use App\Catalog\Application\Characteristic\AdminUseCases\Exception\CharacteristicNotFoundException;
use App\Catalog\Application\Characteristic\AdminUseCases\FindChar\FindCharHandler;
use App\Catalog\Application\Characteristic\AdminUseCases\Input\CharDataDTO;
use App\Catalog\Application\Characteristic\AdminUseCases\ListChars\ListCharsHandler;
use App\Catalog\Application\Characteristic\AdminUseCases\UpdateChar\UpdateCharHandler;
use App\Catalog\Domain\Entity\Characteristic;
use App\Catalog\Domain\Repository\CharacteristicRepositoryInterface;
use App\Shared\Application\FlusherInterface;
use PHPUnit\Framework\TestCase;

final class CharacteristicHandlersTest extends TestCase
{
    public function testCreateHandlerNormalizesAndSavesACharacteristic(): void
    {
        $repository = new InMemoryCharacteristicRepository();
        $flusher = new CharacteristicRecordingFlusher();

        (new CreateCharHandler($repository, $flusher))(new CharDataDTO(
            '  Resolution  ',
            '  MP  ',
            3,
            true,
        ));

        self::assertCount(1, $repository->items);
        $characteristic = $repository->items[0];
        self::assertSame('Resolution', $characteristic->getTitle());
        self::assertSame('MP', $characteristic->getMeasure());
        self::assertSame(3, $characteristic->getPos());
        self::assertTrue($characteristic->getIsActive());
        self::assertSame(1, $flusher->commits);
    }

    public function testCreateHandlerRejectsDuplicateTitleAndMeasure(): void
    {
        $repository = new InMemoryCharacteristicRepository();
        $repository->save(Characteristic::create('Resolution', 'MP', 0, true, new \DateTime()));

        $handler = new CreateCharHandler($repository, new CharacteristicRecordingFlusher());

        $this->expectException(CharAlreadyInUseException::class);
        $handler(new CharDataDTO('Resolution', 'MP', 1, true));
    }

    public function testUpdateHandlerChangesTheCharacteristicAndAllowsItsOwnValues(): void
    {
        $repository = new InMemoryCharacteristicRepository();
        $characteristic = Characteristic::create('Resolution', 'MP', 0, true, new \DateTime());
        $repository->save($characteristic);
        $flusher = new CharacteristicRecordingFlusher();

        (new UpdateCharHandler($repository, $flusher))(1, new CharDataDTO(
            'Weight',
            null,
            5,
            false,
        ));

        self::assertSame('Weight', $characteristic->getTitle());
        self::assertNull($characteristic->getMeasure());
        self::assertSame(5, $characteristic->getPos());
        self::assertFalse($characteristic->getIsActive());
        self::assertSame(1, $flusher->commits);
    }

    public function testUpdateHandlerRejectsAnotherCharacteristicWithTheSameTitleAndMeasure(): void
    {
        $repository = new InMemoryCharacteristicRepository();
        $first = Characteristic::create('Resolution', 'MP', 0, true, new \DateTime());
        $second = Characteristic::create('Weight', 'kg', 1, true, new \DateTime());
        $repository->save($first);
        $repository->save($second);

        $handler = new UpdateCharHandler($repository, new CharacteristicRecordingFlusher());

        $this->expectException(CharAlreadyInUseException::class);
        $handler(1, new CharDataDTO('Weight', 'kg', 2, true));
    }

    public function testUpdateAndDeleteHandlersRejectMissingCharacteristics(): void
    {
        $repository = new InMemoryCharacteristicRepository();

        try {
            (new UpdateCharHandler($repository, new CharacteristicRecordingFlusher()))(
                99,
                new CharDataDTO('Missing', null, 0, true),
            );
            self::fail('Update handler should reject a missing characteristic.');
        } catch (CharacteristicNotFoundException) {
            self::assertTrue(true);
        }

        try {
            (new DeleteCharHandler($repository, new CharacteristicRecordingFlusher()))(99);
            self::fail('Delete handler should reject a missing characteristic.');
        } catch (CharacteristicNotFoundException) {
            self::assertTrue(true);
        }
    }

    public function testDeleteHandlerRemovesAndFindsAndListsCharacteristics(): void
    {
        $repository = new InMemoryCharacteristicRepository();
        $characteristic = Characteristic::create('Resolution', 'MP', 2, true, new \DateTime());
        $repository->save($characteristic);

        $view = (new FindCharHandler($repository))(1);
        self::assertSame(1, $view->id);
        self::assertSame('Resolution', $view->title);
        self::assertSame('MP', $view->measure);

        $views = (new ListCharsHandler($repository))();
        self::assertCount(1, $views);
        self::assertSame('Resolution', $views[0]->title);

        $flusher = new CharacteristicRecordingFlusher();
        (new DeleteCharHandler($repository, $flusher))(1);

        self::assertSame([], $repository->items);
        self::assertSame(1, $flusher->commits);
    }

    public function testFindHandlerRejectsMissingCharacteristic(): void
    {
        $this->expectException(CharacteristicNotFoundException::class);
        (new FindCharHandler(new InMemoryCharacteristicRepository()))(404);
    }
}

final class CharacteristicRecordingFlusher implements FlusherInterface
{
    public int $commits = 0;

    public function commit(): void
    {
        ++$this->commits;
    }
}

final class InMemoryCharacteristicRepository implements CharacteristicRepositoryInterface
{
    /** @var list<Characteristic> */
    public array $items = [];

    public function findBy(string $title, ?string $measure): ?Characteristic
    {
        $title = trim($title);
        $measure = null === $measure || '' === trim($measure) ? null : trim($measure);

        foreach ($this->items as $characteristic) {
            if ($characteristic->getTitle() === $title && $characteristic->getMeasure() === $measure) {
                return $characteristic;
            }
        }

        return null;
    }

    public function findById(int $id): ?Characteristic
    {
        foreach ($this->items as $characteristic) {
            if ($characteristic->getId() === $id) {
                return $characteristic;
            }
        }

        return null;
    }

    public function findAll(): array
    {
        return $this->items;
    }

    public function save(Characteristic $characteristic): void
    {
        if (!\in_array($characteristic, $this->items, true)) {
            $id = new \ReflectionProperty(Characteristic::class, 'id');
            $id->setValue($characteristic, count($this->items) + 1);
            $this->items[] = $characteristic;
        }
    }

    public function delete(Characteristic $characteristic): void
    {
        $this->items = array_values(array_filter(
            $this->items,
            static fn (Characteristic $item): bool => $item !== $characteristic,
        ));
    }
}
