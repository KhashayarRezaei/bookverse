<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="BookVerse API Documentation",
 *     description="A comprehensive API for managing books, orders, payments, and AI-powered features",
 *     @OA\Contact(
 *         email="support@bookverse.com",
 *         name="BookVerse Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="BookVerse API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and authorization endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Books",
 *     description="Book management endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Orders",
 *     description="Order management and payment processing"
 * )
 * 
 * @OA\Tag(
 *     name="AI Features",
 *     description="AI-powered recommendations and summaries"
 * )
 * 
 * @OA\Tag(
 *     name="Admin",
 *     description="Administrative operations (admin only)"
 * )
 */
class SwaggerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/docs",
     *     summary="API Documentation",
     *     description="Access the interactive API documentation",
     *     tags={"Documentation"},
     *     @OA\Response(
     *         response=200,
     *         description="Swagger UI documentation page"
     *     )
     * )
     */
    public function index()
    {
        return redirect('/api/documentation');
    }
} 