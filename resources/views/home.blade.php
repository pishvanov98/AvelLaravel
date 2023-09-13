@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">

            @if($images_slider)
                <div class="owl-carousel owl-theme w-75" id="slider">
                    <!--Слайд 1-->
                    @foreach($images_slider as $image)
                        <div class="slide">
                            <img src="{{asset($image['path'])}}">
                        </div>
                    @endforeach
                </div>
            @endif

            <?php
            if (!empty($Products)){
                ?>
            <div class="filterslider_home">
                <ul>
                    <li data-name="Новинки" data-selectid="GoodsSlaiderHome1" data-link="{{route('entrance')}}" class="item activ_">
                        Новинки
                    </li>
                    <li data-name="Эксклюзивное предложение" data-selectid="GoodsSlaiderHome2" data-link="{{route('exclusive')}}"
                        class="item">Эксклюзивные предложения
                    </li>
                    <li data-name="actiya" data-selectid="GoodsSlaiderHomeAciya" data-link="{{route('action')}}" class="item">
                        Акции
                    </li>
                    <!--<li class="action_prod_home"><a href="/promo?product"> Товар дня </a></li>-->
                </ul>
                <div class="container_carousel">
                    <div id="GoodsSlaiderHome1" class="owl-carousel owl-theme slaider_prod">
                            <?php
                        foreach ($Products as $product) { ?>
                        @include('components.product')
                        <?php } ?>
                                <div class="see_all_slider">
                                    <a href="{{route('entrance')}}">Показать ещё →</a>
                                </div>
                    </div>
                </div>
            </div>
            <?php } ?>


        </div>
    </div>

    @push('script')

        <script type="module">

            $(document).ready(function () {
                const slider = $("#slider").owlCarousel({
                    loop: true,
                    margin: 5,
                    autoplay: true,
                    autoplayTimeout: 10000,
                    nav: false,
                    responsive: {
                        0: {
                            items: 1
                        },
                    }
                });
            });

            jQuery(document).ready(function () {
                $("#GoodsSlaiderHome1").owlCarousel({
                    items: 5,
                    autoPlay: false,
                    lazyLoad: true,
                    navigation: true,
                    margin: 10,
                    nav: true,
                    dots: false,
                    navigationText: ['‹', '›']
                });
            });

            $('.filterslider_home ul li.item').click(function () {
                if ($(this).hasClass("activ_")) {
                    window.location = $(this).data('link');
                    return;
                }
                $(".filterslider_home ul li.item").removeClass('activ_');
                $(this).addClass('activ_');


                var idSlider = $(this).data("selectid");
                idSlider = $("#" + idSlider);

                if (idSlider.length > 0) {
                    $(".filterslider_home div.owl-carousel").addClass('hide_');
                    idSlider.removeClass('hide_');
                    return;
                }
                var name = $(this).data("name");
                var name_id_slider = $(this).data("selectid");

                $.ajax({
                    url: 'api/home/exclusive', //Путь к файлу, который нужно подгрузить https://aveldent.ru/index.php?route=product/search&tag=Эксклюзивное предложение
                    type: 'GET',
                    data: {
                        name: name,
                        idSlider: name_id_slider,
                    },
                    beforeSend: function () {
                        //$("#GoodsSlaiderHome1").addClass('hide_');
                        $(".filterslider_home div.owl-carousel").addClass('hide_');
                        idSlider.removeClass('hide_');
                    },
                    success: function (data) {

                        if (!$("#" + name_id_slider).length) {

                            $('.container_carousel').append(data); //Подгрузка внутрь блока

                            jQuery(document).ready(function () {
                                $("#" + name_id_slider).owlCarousel({
                                    items: 5,
                                    autoPlay: false,
                                    lazyLoad: true,
                                    navigation: true,
                                    margin: 10,
                                    nav: true,
                                    dots: false,
                                    navigationText: ['‹', '›']
                                });
                            });

                        }

                    },
                    error: function () {
                        alert('Error!');
                    }
                });


            });


        </script>
    @endpush

@endsection
