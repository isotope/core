<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig\Studio;

use Contao\CoreBundle\Twig\Studio\Operation\AbstractRenameVariantOperation as CoreAbstractRenameVariantOperationAlias;
use Contao\CoreBundle\Twig\Studio\Operation\OperationContext;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[AutoconfigureTag('container.service_subscriber', ['id' => 'contao.twig.studio.cache_invalidator'])]
abstract class AbstractRenameVariantOperation extends CoreAbstractRenameVariantOperationAlias
{
    #[\Override]
    public function canExecute(OperationContext $context): bool
    {
        if (!$this->userTemplateExists($context) || $context->isThemeContext()) {
            return false;
        }

        return 1 === preg_match('%^'.preg_quote($this->getPrefix(), '%').'/.+$%', $context->getIdentifier());
    }

    #[\Override]
    public function execute(Request $request, OperationContext $context): Response|null
    {
        preg_match('%^('.preg_quote($this->getPrefix(), '%').')/(.+)$%', $context->getIdentifier(), $matches);
        [, $baseIdentifier, $oldFragment] = $matches;

        // Show a confirmation dialog
        if (!$identifierFragment = $request->request->getString('identifier_fragment')) {
            return $this->render('@Contao/backend/template_studio/operation/create_or_rename_variant.stream.html.twig', [
                'operation' => $this->getName(),
                'operation_type' => 'rename',
                'identifier' => $baseIdentifier,
                'extension' => $context->getExtension(),
                'suggested_identifier_fragment' => $oldFragment,
                'allowed_identifier_fragment_pattern' => $this->buildAllowedIdentifierFragmentsPattern($baseIdentifier),
            ]);
        }

        $newIdentifier = "$baseIdentifier/$identifierFragment";
        $newStoragePath = "$newIdentifier.{$context->getExtension()}";

        if ($this->getUserTemplatesStorage()->fileExists($newStoragePath)) {
            return $this->error($context, 'template_studio.message.rename_variant.error');
        }

        // Rename the variant template file
        $this->getUserTemplatesStorage()->move($context->getUserTemplatesStoragePath(), $newStoragePath);

        $this->invalidateTemplateCache($context);
        $this->refreshTemplateHierarchy();

        $this->migrateDatabaseUsages($context->getIdentifier(), $newIdentifier);

        return $this->render('@Contao/backend/template_studio/operation/rename_variant_result.stream.html.twig', [
            'old_identifier' => $context->getIdentifier(),
            'new_identifier' => $newIdentifier,
        ]);
    }

    private function buildAllowedIdentifierFragmentsPattern(string $identifier): string
    {
        $existingVariantNames = array_map(
            static fn (string $variantIdentifier): string => substr($variantIdentifier, \strlen($identifier) + 1),
            array_keys(
                iterator_to_array(
                    $this->getTwigFinder()
                        ->identifier($identifier)
                        ->withVariants(true),
                ),
            ),
        );

        // Disallow selecting a name of an existing variant
        return \sprintf(
            '^(?!(%s)$).*',
            implode('|', $existingVariantNames),
        );
    }

    private function migrateDatabaseUsages(string $from, string $to): void
    {
        $connection = $this->container->get('database_connection');

        foreach ($this->getDatabaseReferencesThatShouldBeMigrated() as $reference) {
            [$table, $field] = explode('.', $reference, 2);

            $connection->update($table, [$field => $to], [$field => $from]);
        }
    }
}
