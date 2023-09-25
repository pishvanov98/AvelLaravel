
    <div class="wrapper_goods">
        @if($Products)

            @foreach($Products as $product)

                @include('components.product')

            @endforeach

        @endif
    </div>
    <div class="pagination_wrapper ajax">  {{$Products->links()}} </div>

    <script>

        @if(!empty($string_art))
        $(document).ready(function() {
            $('.ajax .pagination a').on('click', function (event) {
                event.preventDefault();
                var string_art = '{{$string_art}}';
                var page= $(this).text();
                var search="{{$search}}";
                var category="";
                @if(!empty($category))
                    category= "{{$category}}";
                @endif
                if(page == "›"){
                    page= $('.ajax .pagination .page-item.active .page-link').text();
                    page=Number(page)+1;
                    if(page > $('.ajax .pagination a').length){
                        page=$('.ajax .pagination a').length;
                    }
                }
               if(page == "‹"){
                   page= $('.ajax .pagination .page-item.active .page-link').text();
                   page=Number(page)-1;
                   if(page < 1){
                       page=1;
                   }
               }
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var xhr = $.ajax({
                    url: '{{route('query.filter.product.search')}}',
                    method: 'post',
                    dataType: 'html',
                    data: {string_art: string_art,page:page,search:search,category:category},
                    beforeSend:function (){
                        $('.products_category').empty();
                        $('.spinner').removeClass('hide');
                    },
                    success: function(data){
                        $('.spinner').addClass('hide');
                        $( ".products_category" ).append(data);
                        //kill the request
                        xhr.abort()
                    }
                });
            });
        });
            @else

            $(document).ready(function() {
                $('.ajax .pagination a').on('click', function (event) {
                    console.log('click')
                    event.preventDefault();
                    var page= $(this).text();
                    if(page == "›"){
                        page= $('.ajax .pagination .page-item.active .page-link').text();
                        page=Number(page)+1;
                        if(page > $('.ajax .pagination a').length){
                            page=$('.ajax .pagination a').length;
                        }
                    }
                    if(page == "‹"){
                        page= $('.ajax .pagination .page-item.active .page-link').text();
                        page=Number(page)-1;
                        if(page < 1){
                            page=1;
                        }
                    }
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var xhr = $.ajax({
                        url: '{{route('query.filter.product.search')}}',
                        method: 'post',
                        dataType: 'html',
                        data: {page:page,search:search,category:category},
                        success: function(data){
                            $('.products_category').empty();
                            $( ".products_category" ).append(data);
                            //kill the request
                            xhr.abort()
                        }
                    });


                });
            });
            @endif

    </script>

