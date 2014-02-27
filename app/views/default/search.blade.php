@section('content')

<div class="container main">
	<!-- Search Bar -->
	<div class="container">
		<div class="col-md-7 centered">
			<h1 class="text-center text-info"> {{{ trans("search.help.main") }}} </h1>
			{{ Form::open(['url' => "/search", "class" => "ajax-search"]) }}
				<div class="input-group">
					<input type="text" class="form-control" placeholder="{{{ trans("search.placeholder") }}}" name="q" id="search" value="{{{ $param }}}">
					<div class="input-group-btn">
						<button class="btn btn-info" type="submit">
							{{{ trans("search.button") }}}
						</button>
					</div>
				</div>
			{{ Form::close() }}

		</div>
		<hr>
		<div class="col-md-7 centered">
			<h2 class="text-center text-warning"> {{{ trans("search.help.options") }}} </h2>
		</div>
	</div>

	@if ($search)
		<!-- Search Results -->
		<div class="container">
			<div class="row visible-md visible-lg">
					<div class="col-sm-4 text-center" style="margin-bottom: 10px">
						<h4>{{{ trans("search.metadata.artist") }}}</h4>
					</div>
					<div class="col-sm-4 text-center" style="margin-bottom: 10px">
						<h4>{{{ trans("search.metadata.title") }}}</h4>
					</div>
					<div class="col-sm-1 text-center" style="margin-bottom: 10px">
						<h4>{{{ trans("search.fave") }}}</h4>
					</div>
					<div class="col-sm-3 text-center" style="margin-bottom: 10px">
						<h4>{{{ trans("search.requestable") }}}</h4>
					</div>
			</div>
			<hr style="margin-top: 3px; margin-bottom: 8px;">

			@foreach ($search["data"] as $result)

				<div class="row">
					<div class="col-sm-4 text-center" style="margin-bottom: 10px">
						<span class="text-danger">{{{ $result["artist"] }}}</span>
					</div>
					<div class="col-sm-4 text-center" style="margin-bottom: 10px">
						<span class="text-info">{{{ $result["track"] }}}</span>
					</div>
					<div class="col-sm-1" style="margin-bottom: 10px">
						<button class="btn btn-block btn-danger fave-button" data-toggle="modal" data-target="#faves">
							<i class="fa fa-heart"></i>
						</button>
					</div>
					<div class="col-sm-3" style="margin-bottom: 10px">
						@if ($result["cooldown"])
							<button class="btn btn-block btn-danger request-button disabled">
								{{ trans("search.requestable") . " " . time_ago($result["cooldown"]) }}
							</button>
						@else
							{{ Form::open(["url" => "/request/{$result["id"]}"]) }}
								<button type="submit" name="id" value="{{ $result["id"] }}" class="btn btn-block btn-success request-button">
									{{{ trans("search.request") }}}
								</button>
							{{ Form::close() }}
						@endif
					</div>
				</div>	
				<hr style="margin-top: 3px; margin-bottom: 8px;">
			@endforeach

			<div class="text-center">
				{{ $links }}
			</div>
			

		</div>
	@endif


<div class="modal fade" id="requests">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">{{{ trans("search.request") }}}</h4>
			</div>
			<div class="modal-body"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="faves">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">{{{ trans("search.fave") }}}</h4>
			</div>
			<div class="modal-body">Sorry, faves are not implemented yet.</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

</div>

@stop

@section('script')

@stop
