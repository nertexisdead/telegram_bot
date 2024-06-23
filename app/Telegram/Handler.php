<?php

namespace App\Telegram;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Stringable;

use Illuminate\Support\Facades\Config;

use App\Models\ButtonClick;

class Handler extends WebhookHandler
{
    public function hello(): void
    {
        $this->reply('Привет! Это твой первый бот');
    }

    public function help(): void
    {
        $this->reply(
            "Бот умеет:\n" .
            "- Отправить приветственное сообщение: /hello\n" .
            "- Показать меню действий: /actions\n" .
            "- Поставить лайк: нажать на кнопку 'Поставить лайк'\n" .
            "- Получить количество нажатий: после нажатия на кнопку 'Поставить лайк'\n" .
            "- Узнать твой Telegram ID и текст сообщения: просто отправь сообщение\n" .
            "- Узнать имя и размер файла: отправь изображение"
        );
    }

    public function actions(): void
    {
        $appUrl = Config::get('app.url');
        Telegraph::message('Выбери какое-то действие')
            ->keyboard(
                Keyboard::make()->buttons([
                    Button::make('Перейти на сайт')->url($appUrl),
                    Button::make('Поставить лайк')->action('like'),
                ])
            )->send();
    }


    public function like(): void
    {
        $like = ButtonClick::where('name', 'like')->first();

        if ($like) {
            $like->count++;
        } else {
            $like = new ButtonClick();
            $like->button_name = 'like';
            $like->count = 1;
        }

        $like->save();

        $this->reply("Спасибо за лайк! Количество нажатий: $like->count");
    }

    protected function handleUnknownCommand(Stringable $text): void
    {
        if ($text->value() == '/start') {
            $this->reply('Рад видеть тебя! Давай начнем пользоваться мной :)');
        } else {
            $this->reply('Неизвестная команда');
        }
    }

    protected function handleChatMessage(Stringable $text): void
    {
        if ($this->message) {
            if ($this->message->photos() && $this->message->photos()->isNotEmpty()) {
                // обработка фото
                /** @var \DefStudio\Telegraph\DTO\Photo $photo */
                $photo = $this->message->photos()->first();

                $fileName = $photo->id();
                $fileSize = $photo->fileSize();

                $response = "Имя файла: $fileName\nРазмер файла: $fileSize байт";

            } elseif ($this->message->text()) {
                // Обработка текстового сообщения
                $messageText = $this->message->text();
                $chatId = $this->message->chat()->id();

                $response = "Твой Telegram ID: $chatId\nТы написал: $messageText";
            } else {
                $response = "Извини, не могу обработать сообщение.";
            }
        } else {
            $response = "Извини, не могу обработать сообщение.";
        }

        $this->reply($response);
    }

}
