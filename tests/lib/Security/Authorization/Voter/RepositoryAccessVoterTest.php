<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Security\Authorization\Voter;

use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use Netgen\Layouts\Ibexa\Security\Authorization\Voter\RepositoryAccessVoter;
use Netgen\Layouts\Ibexa\Security\Role\RoleHierarchy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

use function count;

#[CoversClass(RepositoryAccessVoter::class)]
final class RepositoryAccessVoterTest extends TestCase
{
    private MockObject&AccessDecisionManagerInterface $accessDecisionManagerMock;

    private RepositoryAccessVoter $voter;

    protected function setUp(): void
    {
        $roleHierarchy = new RoleHierarchy(
            [
                'ROLE_NGLAYOUTS_ADMIN' => [
                    'ROLE_NGLAYOUTS_EDITOR',
                ],
            ],
        );

        $this->accessDecisionManagerMock = $this->createMock(AccessDecisionManagerInterface::class);

        $this->voter = new RepositoryAccessVoter(
            $roleHierarchy,
            $this->accessDecisionManagerMock,
        );
    }

    /**
     * @param array<string, bool> $repoAccess
     */
    #[DataProvider('voteDataProvider')]
    public function testVote(mixed $attribute, array $repoAccess, int $voteResult): void
    {
        $token = $this->createMock(TokenInterface::class);

        if (count($repoAccess) > 0) {
            $this->accessDecisionManagerMock
                ->method('decide')
                ->with(
                    self::identicalTo($token),
                    self::isType('array'),
                    self::isNull(),
                )->willReturnCallback(
                    static fn (TokenInterface $token, array $attributes) => $repoAccess[$attributes[0]->function],
                );
        }

        $result = $this->voter->vote($token, null, [$attribute]);

        self::assertSame($voteResult, $result);
    }

    public static function voteDataProvider(): iterable
    {
        return [
            // Only matches admin Ibexa function
            ['ROLE_NGLAYOUTS_ADMIN', ['admin' => true], VoterInterface::ACCESS_GRANTED],
            ['ROLE_NGLAYOUTS_ADMIN', ['admin' => false], VoterInterface::ACCESS_DENIED],

            // Matches both admin and editor Ibexa functions
            ['ROLE_NGLAYOUTS_EDITOR', ['editor' => true], VoterInterface::ACCESS_GRANTED],
            ['ROLE_NGLAYOUTS_EDITOR', ['editor' => false, 'admin' => true], VoterInterface::ACCESS_GRANTED],
            ['ROLE_NGLAYOUTS_EDITOR', ['editor' => false, 'admin' => false], VoterInterface::ACCESS_DENIED],

            ['ROLE_NGLAYOUTS_UNKNOWN', [], VoterInterface::ACCESS_DENIED],

            ['ROLE_UNSUPPORTED', [], VoterInterface::ACCESS_ABSTAIN],
            [new Attribute(), [], VoterInterface::ACCESS_ABSTAIN],
        ];
    }
}
