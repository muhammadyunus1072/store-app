@push('js')
    <script src="{{ asset('vendor/imask/imask.min.js') }}"></script>
    <script>
        var imaskMap = [];
        var imaskOption = {
            mask: Number,
            thousandsSeparator: '.',
            radix: ',',
            signed: true,
            scale: 10,
        };

        $(() => {
            init_imask();

            Livewire.hook('commit', ({
                component,
                commit,
                respond,
                succeed,
                fail
            }) => {
                // Equivalent of 'message.sent'

                succeed(({
                    snapshot,
                    effect
                }) => {
                    // Equivalent of 'message.received'

                    queueMicrotask(() => {
                        // Equivalent of 'message.processed'

                        setTimeout(function() {
                            reinit_imask(false);
                        }, 50);
                    })
                })

                fail(() => {
                    // Equivalent of 'message.failed'
                })
            })
        });

        function init_imask() {
            $('.currency').each(function(index, element) {
                const maxAttributeValue = $(element).attr('max');
                const minAttributeValue = $(element).attr('min');
                const maxOption = maxAttributeValue ? parseFloat(maxAttributeValue) : null;
                const minOption = minAttributeValue ? parseFloat(minAttributeValue) : null;

                if (maxOption !== null) {
                    imaskOption.max = maxOption;
                }
                if (minOption !== null) {
                    imaskOption.min = minOption;
                } else {
                    delete imaskOption.min;
                }
                if ($(element).attr('signed') !== undefined) {
                    imaskOption.signed = true;
                    // imaskOption.thousandsSeparator = '.';
                    // // delete imaskOption.radix;
                    // imaskOption.radix = '.';

                }
                // console.log(imaskOption)
                imaskMap.push({
                    element: element,
                    imask: IMask(element, imaskOption),
                })
            });
        }

        function destroy_imask(save_state = true) {
            Object.values(imaskMap).forEach(val => {
                if (save_state) {
                    $(val.element).val(val.imask.unmaskedValue);
                }
                val.imask.destroy();
            });
            imaskMap = [];
        }

        function reinit_imask(save_state = true) {
            destroy_imask(save_state);
            init_imask()
        }
    </script>
@endpush
