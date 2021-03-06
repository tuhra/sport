@extends('admin.layout.app')
@section('content')
    <section class="content-header">
        <h1>
            Posts
        </h1>
        <span class="breadcrumb"><a href='{{ route("textarticle.create") }}' class="btn btn-sm btn-primary"><i class="fa fa-plus-square"></i>&nbsp;&nbsp;Create</a></span>
    </section>
    <div class="content">
        <div class="row">
            {{--<form method="GET">
                <div class="form-group col-sm-3 mmtext">
                    {!! Form::text('name', null, ['class' => 'form-control searchtitle', 'placeholder' => 'Industries name']) !!}
                </div>
                <a href="{!! route('article.index') !!}" class="btn btn-info">Clear</a>
                <button type="submit" class="btn btn-primary btnSearch">Search</button>
            </form>--}}
        </div>
        <div class="clearfix"></div>
        <div class="clearfix"></div>
        <!-- Flash -->  
        @include('flash::message')
        <div class="clearfix"></div>
        <div class="box box-primary">
            <div class="box-body">

                <table class="table table-striped table-hover tbl_repeat" id="sortable">
                    <thead>
                        <th>No.</th>
                        <th>Title</th>
                        <th>Post Image</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th colspan="3">Action</th>
                    </thead>
                    <tbody>
                    <?php $index = 1; ?>
                    @foreach($articles as $article)
                        <tr>
                            <td>{{ $index++ }}</td>
                            <td>{!! $article->title !!}</td>
                            <td>
                                @if(isset($article->media))
                                <img src="{{ asset($article->media->file_path . $article->media->file_name) }}" width="100px;" />
                                @endif
                            </td>
                            <td>{!! showPrettyStatus($article->status) !!}</td>
                            <td>{!! $article->created_at !!}</td>
                            <td>
                            <a href="{!! route('textarticle.show', [$article->id]) !!}"
                               class='btn btn-xs btn-warning'><i class="fa fa-eye"></i>&nbsp;View</a>
                            <a href="{!! route('textarticle.edit', [$article->id]) !!}"
                               class='btn btn-xs btn-primary'><i class="fa fa-edit"></i>&nbsp;Edit</a>
                            <a href="#" type="button" data-id="{{ $article->id }}"
                               class="btn btn-xs btn-danger" data-toggle="modal"
                               data-url="{{ url('admin/textarticle/'.$article->id) }}"
                               data-target="#deleteFormModal"><i
                                    class="fa fa-trash-o"></i>&nbsp;Delete</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="pull-right">
                    {{ $articles->appends($_GET)->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection