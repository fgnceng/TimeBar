<?php


namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\File;
use App\Entity\Article;
use App\Form\Type\TagsInputType;
use Symfony\Component\Form\AbstractType;
use App\Form\Type\DateTimePickerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Vich\UploaderBundle\Form\Type\VichImageType;


class ArticleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // For the full reference of options defined by each form field type
        // see https://symfony.com/doc/current/reference/forms/types.html

        // By default, form fields include the 'required' attribute, which enables
        // the client-side form validation. This means that you can't test the
        // server-side validation errors from the browser. To temporarily disable
        // this validation, set the 'required' attribute to 'false':
        // $builder->add('title', null, ['required' => false, ...]);

        $builder
            ->add('title', null, [
                'attr' => ['autofocus' => true],
                'label' => 'Title',
            ])
            ->add('content', null, [
                'attr' => ['rows' => 10],
                'help' => 'Yours article content...',
                'label' => 'Article Content',
            ])
            ->add('imageFile', FileType::class, array(
                'label'=>"Insert Image",

            ))

            ->add('publishedAt', DateTimePickerType::class, [
                'label' => 'Publish Date',
                'help' => 'Set the date in the future to schedule the blog article publication.',
            ])
            ->add('tags', TagsInputType::class, [
                'label' => ' Article Tag',
                'required' => false,
            ])

        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
