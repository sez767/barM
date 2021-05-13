<?php
namespace Ipol\DPD\DB\Order;

use \Ipol\DPD\Order as DpdOrder;
use \Ipol\DPD\DB\Model as BaseModel;
use \Ipol\DPD\Shipment;

/**
 * Модель одной записи таблицы заказов
 */
class Model extends BaseModel
{
	const SERVICE_VARIANT_D = 'Д';
	const SERVICE_VARIANT_T = 'Т';
	
	/**
	 * Отправление
	 * @var \Ipol\DPD\Shipment
	 */
	protected $shipment;

	/**
	 * Возвращает список статусов и их описаний
	 * 
	 * @return array
	 */
	public static function StatusList()
	{
		return array(
			DpdOrder::STATUS_NEW              => 'Новый заказ, еще не отправлялся в DPD',
			DpdOrder::STATUS_OK               => 'Успешно создан',
			DpdOrder::STATUS_PENDING          => 'Принят, но нуждается в ручной доработке сотрудником DPD',
			DpdOrder::STATUS_ERROR            => 'Не принят, ошибка',
			DpdOrder::STATUS_CANCEL           => 'Заказ отменен',
			DpdOrder::STATUS_CANCEL_PREV      => 'Заказ отменен ранее',
			DpdOrder::STATUS_NOT_DONE         => 'Заказ отменен в процессе доставки',
			DpdOrder::STATUS_DEPARTURE        => 'Посылка находится на терминале приема отправления',
			DpdOrder::STATUS_TRANSIT          => 'Посылка находится в пути (внутренняя перевозка DPD)',
			DpdOrder::STATUS_TRANSIT_TERMINAL => 'Посылка находится на транзитном терминале',
			DpdOrder::STATUS_ARRIVE           => 'Посылка находится на терминале доставки',
			DpdOrder::STATUS_COURIER          => 'Посылка выведена на доставку',
			DpdOrder::STATUS_DELIVERED        => 'Посылка доставлена получателю',
			DpdOrder::STATUS_LOST             => 'Посылка утеряна',
			DpdOrder::STATUS_PROBLEM          => 'C посылкой возникла проблемная ситуация',
			DpdOrder::STATUS_RETURNED         => 'Посылка возвращена с курьерской доставки',
			DpdOrder::STATUS_NEW_DPD          => 'Оформлен новый заказ по инициативе DPD',
			DpdOrder::STATUS_NEW_CLIENT       => 'Оформлен новый заказ по инициативе клиента',
		);
	}

	/**
	 * Возвращает отправку
	 *
	 * @param bool $forced true - создает новый инстанс на основе полей записи
	 * 
	 * @return \Ipol\DPD\Shipment
	 */
	public function getShipment($forced = false)
	{
		if (is_null($this->shipment) || $forced) {
			$this->shipment = new Shipment($this->getTable()->getConfig());
			$this->shipment->setSender($this->senderLocation);
			$this->shipment->setReceiver($this->receiverLocation);
			$this->shipment->setPaymentMethod($this->personeTypeId, $this->paySystemId);
			$this->shipment->setItems($this->orderItems, $this->sumNpp);

			list($selfPickup, $selfDelivery) = array_values($this->getServiceVariant());
			$this->shipment->setSelfPickup($selfPickup);
			$this->shipment->setSelfDelivery($selfDelivery);

			if ($this->isCreated()) {
				$this->shipment->setWidth($this->dimensionWidth);
				$this->shipment->setHeight($this->dimensionHeight);
				$this->shipment->setLength($this->dimensionLength);
				$this->shipment->setWeight($this->cargoWeight);
			}
		}

		return $this->shipment;
	}

	/**
	 * Ассоциирует внешнюю отправку с записью
	 * Происходит заполнение полей записи на основе данных отправки
	 * 
	 * @param \Ipol\DPD\Shipment $shipment
	 * 
	 * @return self
	 */
	public function setShipment(Shipment $shipment)
	{
		$this->shipment         = $shipment;
		$this->senderLocation   = $shipment->getSender()['ID'];
		$this->receiverLocation = $shipment->getReceiver()['ID'];
		$this->cargoWeight      = $shipment->getWeight();
		$this->cargoVolume      = $shipment->getVolume();
		$this->dimensionWidth   = $shipment->getWidth();
		$this->dimensionWidth   = $shipment->getHeight();
		$this->dimensionLength  = $shipment->getLength();
		$this->personeTypeId    = $shipment->getPaymentMethod()['PERSONE_TYPE_ID'];
		$this->paySystemId      = $shipment->getPaymentMethod()['PAY_SYSTEM_ID'];
		$this->orderItems       = $shipment->getItems();
		$this->price            = $shipment->getPrice();
		$this->serviceVariant   = [
			'SELF_PICKUP'   => $shipment->getSelfPickup(),
			'SELF_DELIVERY' => $shipment->getSelfDelivery(),
		];

		return $this;
	}

	/**
	 * Сеттер св-ва ORDER_ITEMS
	 * 
	 * @param array $items
	 */
	public function setOrderItems($items)
	{
		$this->fields['ORDER_ITEMS'] = \serialize($items);

		return $this;
	}

	/**
	 * Геттер св-ва ORDER_ITEMS
	 * 
	 * @return array
	 */
	public function getOrderItems()
	{
		return \unserialize($this->fields['ORDER_ITEMS']);
	}

	/**
	 * Сеттер св-ва NPP
	 * 
	 * @param float $npp
	 * 
	 * @return self
	 */
	public function setNpp($npp)
	{
		$this->fields['NPP']     = $npp;
		$this->fields['SUM_NPP'] = $npp == 'Y' ? $this->price : 0;

		return $this;
	}

	/**
	 * Устанавливает вариант доставки
	 *
	 * @param string $variant
	 * 
	 * @return self
	 */
	public function setServiceVariant($variant)
	{
		$D = self::SERVICE_VARIANT_D;
		$T = self::SERVICE_VARIANT_T;

		if (is_string($variant) && preg_match('~^('. $D .'|'. $T .'){2}$~sUi', $variant)) {
			$this->fields['SERVICE_VARIANT'] = $variant;
		} else if (is_array($variant)) {
			$selfPickup   = $variant['SELF_PICKUP'];
			$selfDelivery = $variant['SELF_DELIVERY'];
		
			$this->fields['SERVICE_VARIANT'] = ''
				. ($selfPickup   ? $T : $D)
				. ($selfDelivery ? $T : $D)
			;
		}

		return $this;
	}

	/**
	 * Возвращает вариант доставки
	 *
	 * @return array
	 */
	public function getServiceVariant()
	{
		$D = self::SERVICE_VARIANT_D;
		$T = self::SERVICE_VARIANT_T;

		return array(
			'SELF_PICKUP'   => mb_substr($this->fields['SERVICE_VARIANT'], 0, 1) == $T,
			'SELF_DELIVERY' => mb_substr($this->fields['SERVICE_VARIANT'], 1, 1) == $T,
		);
	}

	/**
	 * Возвращает флаг доставка от ТЕРМИНАЛА или ДВЕРИ
	 * 
	 * @return bool
	 */
	public function isSelfPickup()
	{
		$serviceVariant = $this->getServiceVariant();
		return $serviceVariant['SELF_PICKUP'];
	}

	/**
	 * Возвращает флаг доставка до ТЕРМИНАЛА или ДВЕРИ
	 * 
	 * @return bool
	 */
	public function isSelfDelivery()
	{
		$serviceVariant = $this->getServiceVariant();
		return $serviceVariant['SELF_DELIVERY'];
	}

	/**
	 * Возвращает текстовое описание статуса заказа
	 *
	 * @return string
	 */
	public function getOrderStatusText()
	{
		$statusList = static::StatusList();
		$ret = $statusList[$this->orderStatus];

		if ($this->orderStatus == DpdOrder::STATUS_ERROR) {
			$ret .= ': '. $this->orderError;
		}

		return $ret;
	}

	/**
	 * Возвращает ифнормацию о тарифе
	 *
	 * @param  bool $forced пересоздать ли экземпляр отгрузки
	 *
	 * @return array
	 */
	public function getTariffDelivery($forced = false)
	{
		return $this->getShipment($forced)->calculator()->calculateWithTariff($this->serviceCode, $this->currency);
	}

	/**
	 * Возвращает стоимость доставки в заказе
	 *
	 * @return float
	 */
	public function getActualPriceDelivery()
	{
		$tariff = $this->getTariffDelivery();
		
		if ($tariff) {
			return $tariff['COST'];
		}

		return false;
	}

	/**
	 * Сеттер для номера заказа, попутно устанавливаем номер отправления
	 *
	 * @param $orderNum
	 * 
	 * @return self
	 */
	public function setOrderNum($orderNum)
	{
		$this->fields['ORDER_NUM']         = $orderNum;
		$this->fields['ORDER_DATE_CREATE'] = $orderNum ? date('Y-m-d H:i:s') : null;

		return $this;
	}

	/**
	 * Сеттер для статуса заказа
	 * 
	 * @param $orderStatus
	 * @param $orderStatusDate
	 * 
	 * @return self
	 */
	public function setOrderStatus($orderStatus, $orderStatusDate = false)
	{
		if (empty($orderStatus)) {
			return;
		}

		if (!array_key_exists($orderStatus, self::StatusList())) {
			return;
		}

		$this->fields['ORDER_STATUS'] = $orderStatus;
		$this->fields['ORDER_DATE_STATUS'] = $orderStatusDate ?: date('Y-m-d H:i:s');

		if ($orderStatus == DpdOrder::STATUS_CANCEL) {
			$this->fields['ORDER_DATE_CANCEL'] = $orderStatusDate ?: date('Y-m-d H:i:s');
		}
	}

	/**
	 * Возвращает флаг новый заказ
	 * 
	 * @return bool
	 */
	public function isNew()
	{
		return $this->fields['ORDER_STATUS'] == DpdOrder::STATUS_NEW;
	}

	/**
	 * Проверяет отправлялся ли заказ в DPD
	 *
	 * @return bool
	 */
	public function isCreated()
	{
		return $this->fields['ORDER_STATUS'] != DpdOrder::STATUS_NEW
			&& $this->fields['ORDER_STATUS'] != DpdOrder::STATUS_CANCEL;
	}

	/**
	 * Проверяет отправлялся ли заказ в DPD и был ли он там успешно создан
	 *
	 * @return bool
	 */
	public function isDpdCreated()
	{
		return $this->isCreated() && !empty($this->fields['ORDER_NUM']);
	}

	/**
	 * Возвращает инстанс для работы с внешним заказом
	 *
	 * @return \Ipol\DPD\Order;
	 */
	public function dpd()
	{
		return new DpdOrder($this);
	}
}