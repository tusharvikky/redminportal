@extends('redminportal::layouts.master')

@section('content')
    <div class="nav-controls text-right">
        @if ($announcements)
        <span class="label label-default pull-left">
            {{ $announcements->getFrom() . ' to ' . $announcements->getTo() . ' ( total ' . $announcements->getTotal() . ' )' }}
        </span>
        @endif
        {{ HTML::link('admin/announcements/create', 'Create New', array('class' => 'btn btn-primary')) }}
    </div>
    
    @if ($announcements)
        <table class='table table-striped table-bordered'>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Private</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($announcements as $announcement)
                <tr>
                    <td>{{ $announcement->title }}</td>
                    <td>{{ $announcement->created_at }}</td>
                    <td>{{ $announcement->updated_at }}</td>
                    <td>
                        @if ($announcement->private)
                            <span class="label label-success"><span class='glyphicon glyphicon-ok'></span></span>
                        @else
                            <span class="label label-danger"><span class='glyphicon glyphicon-remove'></span></span>
                        @endif
                    </td>
                    <td>
						<div class="btn-group">
						  	<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
						    	Action <span class="caret"></span>
						  	</button>
						  	<ul class="dropdown-menu pull-right" role="menu">
						        <li>
						            <a href="{{ URL::to('admin/announcements/edit/' . $announcement->id) }}">
						                <i class="glyphicon glyphicon-edit"></i>Edit</a>
						        </li>
						        <li>
						            <a href="{{ URL::to('admin/announcements/delete/' . $announcement->id) }}" class="btn-confirm">
						                <i class="glyphicon glyphicon-remove"></i>Delete</a>
						        </li>
						  	</ul>
						</div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $announcements->links() }}
    @else
        <p>No announcement found</p>
    @endif
@stop

@section('footer')
    <script>
        !function ($) {
            $(function(){
                // Add pagination class to ul
                $('div.pagination > ul').addClass('pagination');
                $('div.pagination').removeClass('pagination').addClass('text-center');
            })
        }(window.jQuery);
    </script>
@stop