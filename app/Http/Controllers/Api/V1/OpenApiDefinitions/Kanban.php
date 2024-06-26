<?php
namespace App\Http\Controllers\Api\V1\OpenApiDefinitions;

/**
* @OA\Get(
*      path="/v1/kanbans?cn={owner_cn}",
*      operationId="getUserKanbans",
*      tags={"Kanban v1"},
*      summary="Get user kanbans",
*      description="Returns a collection of users kanban objects",
*      security={
*           {"passport": {"*"}},
*      },
*      @OA\Parameter(
*          name="owner_cn",
*          description="Owner Cn",
*          required=true,
*          in="path",
*          @OA\Schema(
*              type="string"
*          )
*      ),
*      @OA\Response(
*          response=200,
*          description="successful operation",
*          @OA\Schema(ref="#/components/schemas/Kanbans"),
*       ),
*       @OA\Response(response=400, description="Bad request"),
* )
*
* @OA\Post(
*      path="/v1/kanbans",
*      operationId="createKanban",
*      tags={"Kanban v1"},
*      summary="Create new kanban",
*      description="Returns a the new kanban object",
*      security={
*           {"passport": {"*"}},
*      },
*      @OA\RequestBody(
*       required=true,
*       @OA\MediaType(
*           mediaType="application/x-www-form-urlencoded",
*           @OA\Schema(
*               type="object",
*               required={"title, owner_cn"},
*               @OA\Property(
*                   property="title",
*                   description="Kanban title",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="description",
*                   description="Kanban description",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="color",
*                   description="Color",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="owner_cn",
*                   description="Owner Cn",
*                   type="string"
*               )
*           )
*       )
*   ),
*
*      @OA\Response(
*          response=200,
*          description="successful operation",
*          @OA\JsonContent(ref="#/components/schemas/Kanban"),
*       ),
*       @OA\Response(response=400, description="Bad request"),
*
* )
*
* @OA\Put(
*      path="/v1/kanbans/{kanban}",
*      operationId="updateKanbanById",
*      tags={"Kanban v1"},
*      summary="Edit kanban",
*      description="Edit kanban",
*      security={
*           {"passport": {"*"}},
*      },
*      @OA\Parameter(
*          name="kanban",
*          description="Kanban id",
*          required=true,
*          in="path",
*          @OA\Schema(
*              type="integer"
*          )
*      ),
*      @OA\RequestBody(
*       required=true,
*       @OA\MediaType(
*           mediaType="application/x-www-form-urlencoded",
*           @OA\Schema(
*               type="object",
*               @OA\Property(
*                   property="title",
*                   description="Kanban title",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="description",
*                   description="Kanban description",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="color",
*                   description="color",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="owner_cn",
*                   description="Owner Cn",
*                   type="string"
*               )
*           )
*       )
*   ),
*
*       @OA\Response(
*          response=200,
*          description="successful operation",
*          @OA\JsonContent(ref="#/components/schemas/Kanban")
*       ),
*       @OA\Response(response=400, description="Bad request"),
*
* )
*
* @OA\Get(
*      path="/v1/kanbans/{kanban}",
*      operationId="getKanban",
*      tags={"Kanban v1"},
*      summary="Get kanban by Id",
*      description="Returns a kanban object",
*      security={
*           {"passport": {"*"}},
*      },
*      @OA\Parameter(
*          name="kanban",
*          description="Kanban id",
*          required=true,
*          in="path",
*          @OA\Schema(
*              type="integer"
*          )
*      ),
*      @OA\Response(
*          response=200,
*          description="successful operation",
*          @OA\JsonContent(ref="#/components/schemas/Kanban"),
*       ),
*       @OA\Response(response=400, description="Bad request"),
* )
*
* @OA\Delete(
*      path="/v1/kanbans/{kanban}",
*      operationId="deleteKanban",
*      tags={"Kanban v1"},
*      summary="Delete kanban by Id",
*      description="Delete a kanban object",
*      security={
*           {"passport": {"*"}},
*      },
*      @OA\Parameter(
*          name="kanban",
*          description="Kanban Id",
*          required=true,
*          in="path",
*          @OA\Schema(
*              type="integer"
*          )
*      ),
*      @OA\Parameter(
*          name="owner_cn",
*          description="Owner Cn",
*          required=true,
*          in="path",
*          @OA\Schema(
*              type="string"
*          )
*      ),
*
*      @OA\Response(
*          response=200,
*          description="successful operation",
*       ),
*       @OA\Response(response=400, description="Bad request"),
*
* )
*
*/

class Kanban
{
}
