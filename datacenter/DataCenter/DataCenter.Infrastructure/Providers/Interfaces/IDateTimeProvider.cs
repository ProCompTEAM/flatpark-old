using System;

namespace DataCenter.Infrastructure.Providers.Interfaces
{
    public interface IDateTimeProvider
    {
        DateTime Now { get; }
    }
}
