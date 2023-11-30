<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\RequestValidators\UploadReceiptRequestValidator;
use App\Services\ReceiptService;
use App\Services\TransactionService;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Psr7\Stream;

class ReceiptController
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly ReceiptService $receiptService,
        private readonly TransactionService $transactionService
    ) {
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        /** @var UploadedFileInterface $file */
        $file     = $this->requestValidatorFactory->make(UploadReceiptRequestValidator::class)->validate(
            $request->getUploadedFiles()
        )['receipt'];
        $filename = $file->getClientFilename();

        $id = (int) $args['id'];

        if (! $id || ! ($transaction = $this->transactionService->getById($id))) {
            return $response->withStatus(404);
        }

        $randomFilename = bin2hex(random_bytes(25));
        //TODO После получения уникального имени - файлы почему то сохраняются с 0 размером
        $this->filesystem->write('receipts/' . $randomFilename, $file->getStream()->getContents());

        $this->receiptService->create($transaction, $filename, $randomFilename, $file->getClientMediaType());

        return $response;
    }

    public function download(Request $request, Response $response, array $args): Response
    {
        $transationId = (int) $args['transactionId'];
        $receiptId = (int) $args['id'];

        if (! $transationId || ! $this->transactionService->getById($transationId)) {
            return $response->withStatus(404);
        }

        if (! $receiptId || ! ($receipt = $this->receiptService->getById($receiptId))) {
            return $response->withStatus(404);
        }

        if ($receipt->getTransaction()->getId() !== $transationId) {
            return $response->withStatus(401);
        }

        $file = $this->filesystem->readStream('receipts/' . $receipt->getStorageFilename());

        $response = $response->withHeader(
            'Content-Disposition',
            'inline; filename="' . $receipt->getFilename() . '"'
        )->withHeader('Content-Type', $receipt->getMediaType());

        return $response->withBody(new Stream($file));
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        // TODO

        return $response;
    }
}
