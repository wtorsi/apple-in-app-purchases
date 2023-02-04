<?php
declare(strict_types=1);

namespace Api\Apple\Form;

use Api\Apple\Form\Data\ProcessReceiptDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProcessReceiptForm extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProcessReceiptDto::class,
            'method' => 'POST',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('transactionId', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('productId', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('receiptData', TextType::class, ['constraints' => new NotBlank()]);
    }
}