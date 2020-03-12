<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Yves\DummyMarketplacePayment\Form;

use Generated\Shared\Transfer\DummyMarketplacePaymentTransfer;
use Spryker\Shared\DummyMarketplacePayment\DummyMarketplacePaymentConfig;
use Spryker\Yves\StepEngine\Dependency\Form\AbstractSubFormType;
use Spryker\Yves\StepEngine\Dependency\Form\SubFormInterface;
use Spryker\Yves\StepEngine\Dependency\Form\SubFormProviderNameInterface;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class InvoiceSubForm extends AbstractSubFormType implements SubFormInterface, SubFormProviderNameInterface
{
    public const FIELD_DATE_OF_BIRTH = 'date_of_birth';
    public const MIN_BIRTHDAY_DATE_STRING = '-18 years';

    protected const PAYMENT_METHOD = 'invoice';

    /**
     * @return string
     */
    public function getProviderName(): string
    {
        return DummyMarketplacePaymentConfig::PAYMENT_PROVIDER_NAME;
    }

    /**
     * @return string
     */
    protected function getTemplatePath(): string
    {
        return DummyMarketplacePaymentConfig::PAYMENT_PROVIDER_NAME . '/' . static::PAYMENT_METHOD;
    }

    /**
     * @return string
     */
    public function getPropertyPath(): string
    {
        return DummyMarketplacePaymentConfig::PAYMENT_METHOD_DUMMY_MARKETPLACE_PAYMENT_INVOICE;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return DummyMarketplacePaymentConfig::PAYMENT_METHOD_DUMMY_MARKETPLACE_PAYMENT_INVOICE;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DummyMarketplacePaymentTransfer::class,
        ])->setRequired(self::OPTIONS_FIELD_NAME);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addDateOfBirth($builder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addDateOfBirth(FormBuilderInterface $builder)
    {
        $builder->add(
            static::FIELD_DATE_OF_BIRTH,
            BirthdayType::class,
            [
                'label' => 'dummyPaymentInvoice.date_of_birth',
                'required' => true,
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'input' => 'string',
                'attr' => [
                    'placeholder' => 'customer.birth_date',
                ],
                'constraints' => [
                    $this->createNotBlankConstraint(),
                    $this->createBirthdayConstraint(),
                ],
            ]
        );

        return $this;
    }

    /**
     * @return \Symfony\Component\Validator\Constraint
     */
    protected function createNotBlankConstraint(): Constraint
    {
        return new NotBlank(['groups' => $this->getPropertyPath()]);
    }

    /**
     * @return \Symfony\Component\Validator\Constraint
     */
    protected function createBirthdayConstraint(): Constraint
    {
        return new Callback([
            'callback' => function ($date, ExecutionContextInterface $context) {
                if (strtotime($date) > strtotime(static::MIN_BIRTHDAY_DATE_STRING)) {
                    $context->addViolation('checkout.step.payment.must_be_older_than_18_years');
                }
            },
            'groups' => $this->getPropertyPath(),
        ]);
    }
}
