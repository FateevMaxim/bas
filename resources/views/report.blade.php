@if(isset($config->title_text)) @section( 'title_text', $config->title_text ) @endif
<x-app-layout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="grid grid-cols-1 mx-auto md:grid-cols-1 h-22 pl-6 pr-6 pb-4">


                    <form method="POST" action="{{ route('track_report') }}" class="mb-4">
                        <div class="grid grid-cols-6 md:grid-cols-6 gap-2 mb-6 ">
                            <div>
                                <input type="date" id="date" value="{{ $date }}" name="date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            </div>
                            <div class="col-span-2">
                                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">Фильтр</button>
                                <a href="" id="a"><button type="button" id="excel" class="text-white ml-2 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">Экспорт в Excel</button></a>
                            </div>
                            <div>
                                @if(isset($count))<button type="button" class="focus:ring-4 border-2 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">{{ $count }} результат(-а)</button> @endif
                            </div>
                        </div>
                    @if(session()->has('error'))
                        <div id="alert-2" class="flex mr-6 ml-6 p-4 mb-4 text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                            <div class="ml-3 text-sm font-medium">
                                {{ session()->get('error') }}
                            </div>
                            <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex h-8 w-8 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-2" aria-label="Close">
                                <span class="sr-only">Закрыть</span>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            </button>
                        </div>
                    @endif

                        <script>
                            $(document).ready(function(){
                                date = $("#date").val();
                                $("#a").attr("href", 'file-export?date='+date)
                            });



                            /* прикрепить событие submit к форме */
                            $("#filter").submit(function(event) {
                                /* отключение стандартной отправки формы */
                                event.preventDefault();

                                /* собираем данные с элементов страницы: */
                                var $form = $( this ),
                                    date = $("#date").val();
                                    url = $form.attr( 'action' );


                                /* отправляем данные методом POST */
                                $.post( url, { date: date} )
                                 .done(function( data ) {
                                     alert(data)
                                 });

                            });




                        /*    /!* прикрепить событие submit к форме *!/
                            $("#excel").click(function(event) {
                                /!* отключение стандартной отправки формы *!/
                                event.preventDefault();
/!* собираем данные с элементов страницы: *!/

                                date = $("#date").val();
                                city = $("#city").val();
                                url = 'file-export';

                                /!* отправляем данные методом POST *!/
                                $.post( url, { date: date, city: city} )
                                    .done(function( data ) {
                                        //location.reload();
                                    });

                            });*/

                        </script>
                </div>
        </div>
</x-app-layout>
