<?php

namespace App\Entity;

class Basket
{

    private $basketLines = [];

    private $total;

    public function getBasketLines(): ?array
    {
        return $this->basketLines;
    }

    public function setBasketLines(?array $basketLines): self
    {
        $this->basketLines = $basketLines;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getCount(): ?int
    {
        $nbArticles = 0;
        foreach ($this->basketLines as $key => $quantite) {
            $nbArticles += $quantite;
        }
        return $nbArticles;
    }

    public function modifBasketLines(int $id, float $price, int $nb = null)
    {
        if (func_num_args() == 3) {
            $this->total += ($price * $nb);
            if (!isset($this->basketLines[$id])) {
                //create a new line and add it if empty
                $this->basketLines[$id] = $nb;
            } else {
                $this->basketLines[$id] += $nb;
                if ($this->basketLines[$id] == 0) {
                    unset($this->basketLines[$id]);
                }
            }
        } else {
            $this->total -= ($price * $this->basketLines[$id]);
            unset($this->basketLines[$id]);
        }
    }

}
