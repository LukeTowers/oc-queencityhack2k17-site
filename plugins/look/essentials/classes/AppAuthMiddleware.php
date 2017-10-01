<?php namespace Look\Essentials\Classes;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class AppAuthMiddleware
 */
class AppAuthMiddleware
{
    /**
     * Handle requests
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
	    $path = $request->path();
	    $securedPrefix = 'app/';
	    
        // TODO: Gotta be a better way to do this.
	    if (Auth::check() && ($path === 'app/account/login' || $path === 'app/account/register' || $path === 'app/account/reset-password' || $path === 'app/login')) {
		    return redirect('/app/dashboard');
	    }
	    
	    $ignore = [
		    'account/login',
		    'account/register',
            'account/reset-password',
            'login',
	    ];
	    
        if (starts_with($path, $securedPrefix)) {
	        // Get the relative path without the securedPrefix
	        $relativePath = substr($path, strlen($securedPrefix));
	        
            // see if we are ignoring this path by seeing if it starts with
            // the ignore path
            $matched = array_filter($ignore, function($ignorePath) use ($relativePath) {
                return starts_with($relativePath, $ignorePath);
            });
            
	        // If we aren't ignoring this relative path, continue
	        if (count($matched) === 0) {
		        // If user not logged in
		        if (!Auth::check()) {
			        return redirect('/app/account/login');
		        }
	        }
        }
        
        return $next($request);
    }
}
