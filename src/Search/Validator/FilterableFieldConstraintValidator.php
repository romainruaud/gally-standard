<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @package   Elasticsuite
 * @author    ElasticSuite Team <elasticsuite@smile.fr>
 * @copyright 2022 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

declare(strict_types=1);

namespace Elasticsuite\Search\Validator;

use Elasticsuite\Metadata\Model\SourceField;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FilterableFieldConstraintValidator extends ConstraintValidator
{
    /**
     * @param ?SourceField $value
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof FilterableFieldConstraint) {
            throw new UnexpectedTypeException($constraint, FilterableFieldConstraint::class);
        }

        if ($value && !$value->getIsFilterable()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ sourceFieldCode }}', $value->getCode())
                ->addViolation();
        }
    }
}
