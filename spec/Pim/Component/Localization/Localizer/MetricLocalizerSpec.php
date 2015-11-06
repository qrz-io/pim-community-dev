<?php

namespace spec\Pim\Component\Localization\Localizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\LocalizationBundle\Validator\Constraints\IsNumber;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MetricLocalizerSpec extends ObjectBehavior
{
    function let(ValidatorInterface $validator)
    {
        $this->beConstructedWith($validator, ['pim_catalog_metric']);
    }

    function it_is_a_localizer()
    {
        $this->shouldImplement('Pim\Component\Localization\Localizer\LocalizerInterface');
    }

    function it_supports_attribute_type()
    {
        $this->supports('pim_catalog_metric')->shouldReturn(true);
        $this->supports('pim_catalog_number')->shouldReturn(false);
        $this->supports('pim_catalog_price_collection')->shouldReturn(false);
    }

    function it_valids_the_format()
    {
        $this->validate(['data' => '10.05', 'unit' => 'KILOGRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => '-10.05', 'unit' => 'KILOGRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => '10', 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => '-10', 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => 10, 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => 10.05, 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => ' 10.05 ', 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => null, 'unit' => null], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => '', 'unit' => ''], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => 0, 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
        $this->validate(['data' => '0', 'unit' => 'GRAM'], ['decimal_separator' => '.'], 'metric')
            ->shouldReturn(null);
    }

    function it_returns_a_constraint_if_the_format_is_not_valid(
        $validator,
        ConstraintViolationListInterface $constraints
    ) {
        $number = new IsNumber(['decimalSeparator' => '.', 'path' => 'metric']);
        $validator->validate('1,5', $number)->willReturn($constraints);
        $this->validate('1,5', ['decimal_separator' => '.'], 'metric')->shouldReturn($constraints);
    }

    function it_convert_comma_to_dot_separator()
    {
        $this->delocalize(['data' => '10,05', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '10.05', 'unit' => 'GRAM']);

        $this->delocalize(['data' => '-10,05', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '-10.05', 'unit' => 'GRAM']);

        $this->delocalize(['data' => '10', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '10', 'unit' => 'GRAM']);

        $this->delocalize(['data' => '-10', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '-10', 'unit' => 'GRAM']);

        $this->delocalize(['data' => 10, 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => 10, 'unit' => 'GRAM']);

        $this->delocalize(['data' => 10.0585, 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '10.0585', 'unit' => 'GRAM']);

        $this->delocalize(['data' => ' 10.05 ', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => ' 10.05 ', 'unit' => 'GRAM']);

        $this->delocalize(['data' => null, 'unit' => null], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => null, 'unit' => null]);

        $this->delocalize(['data' => '', 'unit' => ''], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '', 'unit' => '']);

        $this->delocalize(['data' => 0, 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => 0, 'unit' => 'GRAM']);

        $this->delocalize(['data' => '0', 'unit' => 'GRAM'], ['decimal_separator' => '.'])
            ->shouldReturn(['data' => '0', 'unit' => 'GRAM']);

        $this->delocalize([], ['decimal_separator' => '.'])
            ->shouldReturn([]);
    }

    function it_throws_an_exception_if_decimal_separator_is_missing()
    {
        $exception = new MissingOptionsException('The option "decimal_separator" do not exist.');
        $this->shouldThrow($exception)
            ->during('validate', [['data' => '10.00'], [], 'metric']);

        $this->shouldThrow($exception)
            ->during('validate', [['data' => '10.00'], ['decimal_separator' => null], 'metric']);

        $this->shouldThrow($exception)
            ->during('validate', [['data' => '10.00'], ['decimal_separator' => ''], 'metric']);
    }
}
