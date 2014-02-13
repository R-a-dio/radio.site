<?php

class BaseController extends Controller {

	public function __construct() {

		// ALL POST/PUT/DELETE REQUIRE CSRF TOKENS.
		$this->beforeFilter('csrf', ['on' => ['post', 'put', 'delete']]);

	}

	protected function getStatus() {
		$status = DB::table('streamstatus')->first();
		$status["dj"] = DB::table("djs")
			->where("id", "=", $status["djid"])
			->first();
		unset($status["djid"]);

		return $status;
	}

	protected function getCurrentSong() {
		return $this->getStatus()["np"];
	}

	/**
	 * Retrieve the current theme's identifier.
	 *
	 * @return string
	 */
	protected function getTheme() {
		// TODO: check database access, DJ column will have theme
		return "valentines";
	}

	/**
	 * Retrieve a page using the current theme.
	 *
	 * @param string $page
	 * @return string
	 */
	protected function theme($page) {
		// TODO: check database access, DJ column will have theme
		$theme = $this->getTheme();
		$path = app_path() . "/views/$theme/$page.blade.php";

		if (!Cache::has("theme:$path")) {

			Cache::put("theme:$path", file_exists($path), 60);

		}

		if (!Cache::get("theme:$path")) {
			$theme = "default";
		}
		
		return $theme . "." . $page;
	}

	/**
	 * Setup the layout used by the controller.
	 * Also adds a few required variables.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			// TODO: dynamic source for the themes
			View::share("theme", $this->getTheme());
			View::share("status", $this->getStatus());
			View::share("script", $this->theme("script"));

			if (Request::ajax() or Input::get("ajax") == "potato")
				$this->layout = "ajax";

			$this->layout = View::make($this->layout);
		}
	}

}