using DataCenter.Infrastructure.Providers.Interfaces;
using System;

namespace DataCenter.Infrastructure.Providers
{
    public class DateTimeProvider : IDateTimeProvider, IProvider
    {
        public DateTime Now => DateTime.Now;
    }
}
